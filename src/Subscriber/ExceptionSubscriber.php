<?php declare(strict_types=1);

namespace Tinect\Redirects\Subscriber;

use Shopware\Core\Content\Category\Exception\CategoryNotFoundException;
use Shopware\Core\Content\Seo\SeoUrlPlaceholderHandlerInterface;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\SalesChannelRequest;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Tinect\Redirects\Content\Redirect\RedirectEntity;

class ExceptionSubscriber implements EventSubscriberInterface
{
    private EntityRepositoryInterface $redirectRepository;

    private SeoUrlPlaceholderHandlerInterface $seoUrlPlaceholderHandler;

    public function __construct(
        EntityRepositoryInterface $redirectRepository,
        SeoUrlPlaceholderHandlerInterface $seoUrlPlaceholderHandler
    ) {
        $this->redirectRepository = $redirectRepository;
        $this->seoUrlPlaceholderHandler = $seoUrlPlaceholderHandler;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();

        if (!$request->attributes->get(SalesChannelRequest::ATTRIBUTE_IS_SALES_CHANNEL_REQUEST)) {
            return;
        }

        $exception = $event->getThrowable();

        if (!($exception instanceof NotFoundHttpException) && !($exception instanceof CategoryNotFoundException)) {
            return;
        }

        $salesChannelDomainId = $request->attributes->get(SalesChannelRequest::ATTRIBUTE_DOMAIN_ID);

        $response = $this->handleRequest($request->getPathInfo(), $salesChannelDomainId);

        if ($response instanceof Response) {
            $event->setResponse($response);
        }
    }

    private function handleRequest(string $path, ?string $salesChannelDomainId): ?Response
    {
        $context = new Context(new SystemSource());

        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter('source', $path))
            ->addFilter(new EqualsAnyFilter('salesChannelDomainId', [$salesChannelDomainId, null]))
            ->addSorting(new FieldSorting('salesChannelDomainId', FieldSorting::DESCENDING))
            ->setLimit(1);

        /** @var RedirectEntity $redirect */
        $redirect = $this->redirectRepository->search($criteria, $context)->first();

        if (!$redirect) {
            $this->redirectRepository->create([[
                'id' => Uuid::randomHex(),
                'source' => $path,
                'salesChannelDomainId' => $salesChannelDomainId,
            ]], $context);

            return null;
        }

        if ($redirect->active === false) {
            return null;
        }

        $targetURL = $redirect->target;

        //TODO $this->seoUrlPlaceholderHandler->replace over $targetURL

        return new RedirectResponse($targetURL, $redirect->httpCode);
    }
}
