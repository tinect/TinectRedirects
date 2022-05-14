<?php declare(strict_types=1);

namespace Tinect\Redirects\Subscriber;

use Shopware\Core\Content\Category\Exception\CategoryNotFoundException;
use Shopware\Core\Content\Seo\SeoUrlPlaceholderHandlerInterface;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\PlatformRequest;
use Shopware\Core\SalesChannelRequest;
use Shopware\Core\System\SalesChannel\Context\AbstractSalesChannelContextFactory;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextService;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Framework\Routing\RequestTransformer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Tinect\Redirects\Content\Redirect\RedirectEntity;

class ExceptionSubscriber implements EventSubscriberInterface
{
    private EntityRepositoryInterface $redirectRepository;

    private SeoUrlPlaceholderHandlerInterface $seoUrlPlaceholderHandler;

    private AbstractSalesChannelContextFactory $salesChannelContextFactory;

    public function __construct(
        EntityRepositoryInterface $redirectRepository,
        SeoUrlPlaceholderHandlerInterface $seoUrlPlaceholderHandler,
        AbstractSalesChannelContextFactory $salesChannelContextFactory
    ) {
        $this->redirectRepository = $redirectRepository;
        $this->seoUrlPlaceholderHandler = $seoUrlPlaceholderHandler;
        $this->salesChannelContextFactory = $salesChannelContextFactory;
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

        if ($request->getMethod() !== Request::METHOD_GET) {
            return;
        }

        if (!$request->attributes->get(SalesChannelRequest::ATTRIBUTE_IS_SALES_CHANNEL_REQUEST)) {
            return;
        }

        $exception = $event->getThrowable();

        if (!($exception instanceof NotFoundHttpException) && !($exception instanceof CategoryNotFoundException)) {
            return;
        }

        $salesChannelDomainId = $request->attributes->get(SalesChannelRequest::ATTRIBUTE_DOMAIN_ID);

        $response = $this->handleRequest($request, $salesChannelDomainId);

        if ($response instanceof Response) {
            $event->setResponse($response);
        }
    }

    private function handleRequest(Request $request, ?string $salesChannelDomainId): ?Response
    {
        $path = $request->getPathInfo();

        // do not track bot requests building useless urls with "https://oldurl'https://newurl'"
        if (preg_match('/(.?)\'(.*)\'/', $path)) {
            return null;
        }

        $context = new Context(new SystemSource());

        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter('source', $path))
            ->addFilter(new MultiFilter(MultiFilter::CONNECTION_OR, [
                new EqualsFilter('salesChannelDomainId', $salesChannelDomainId),
                new EqualsFilter('salesChannelDomainId', null),
            ]))
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

        $host = $request->attributes->get(RequestTransformer::SALES_CHANNEL_ABSOLUTE_BASE_URL)
            . $request->attributes->get(RequestTransformer::SALES_CHANNEL_BASE_URL);

        $salesChannelContext = $this->getSalesChannelContext($request);

        $targetURL = $this->seoUrlPlaceholderHandler->replace($targetURL, $host, $salesChannelContext);

        return new RedirectResponse($targetURL, $redirect->httpCode);
    }

    private function getSalesChannelContext(Request $request): SalesChannelContext
    {
        $salesChannelContext = $request->attributes->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_CONTEXT_OBJECT);

        if ($salesChannelContext) {
            return $salesChannelContext;
        }

        return $this->createSalesChannelContext(
            $request->attributes->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_ID),
            $request->headers->get(PlatformRequest::HEADER_LANGUAGE_ID)
        );
    }

    private function createSalesChannelContext(string $salesChannelId, string $languageId): SalesChannelContext
    {
        return $this->salesChannelContextFactory->create('', $salesChannelId, [SalesChannelContextService::LANGUAGE_ID => $languageId]);
    }
}
