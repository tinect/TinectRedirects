<?php declare(strict_types=1);

namespace Tinect\Redirects\Subscriber;

use Shopware\Core\Content\Category\Exception\CategoryNotFoundException;
use Shopware\Core\Content\Seo\SeoUrlPlaceholderHandlerInterface;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\PlatformRequest;
use Shopware\Core\SalesChannelRequest;
use Shopware\Core\System\SalesChannel\Context\AbstractSalesChannelContextFactory;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextFactory;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextService;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Framework\Routing\RequestTransformer;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\MessageBusInterface;
use Tinect\Redirects\Content\Redirect\RedirectEntity;
use Tinect\Redirects\Message\TinectRedirectUpdateMessage;

class ExceptionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly EntityRepository $tinectRedirectsRedirectRepository,
        private readonly SeoUrlPlaceholderHandlerInterface $seoUrlPlaceholderHandler,
        #[Autowire(service: SalesChannelContextFactory::class)] private readonly AbstractSalesChannelContextFactory $salesChannelContextFactory,
        private readonly MessageBusInterface $messageBus,
    ) {
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

        $salesChannelDomainId = $request->attributes->getString(SalesChannelRequest::ATTRIBUTE_DOMAIN_ID);

        $response = $this->handleRequest($request, $salesChannelDomainId);

        if ($response instanceof Response) {
            $event->setResponse($response);
        }
    }

    private function handleRequest(Request $request, string $salesChannelDomainId): ?Response
    {
        $path = $request->getPathInfo();

        // do not track bot requests building useless urls with "https://oldurl'https://newurl'"
        if (preg_match('/(.?)\'(.*)\'/', $path)) {
            return null;
        }

        if (empty($salesChannelDomainId)) {
            $salesChannelDomainId = null;
        }

        $message = new TinectRedirectUpdateMessage(
            source: $path,
            salesChannelDomainId:  $salesChannelDomainId,
            ipAddress: $request->getClientIp() ?? '',
            userAgent: $request->headers->get('User-Agent') ?? '',
        );

        $context = new Context(new SystemSource());

        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter('source', $path))
            ->addFilter(new MultiFilter(MultiFilter::CONNECTION_OR, [
                new EqualsFilter('salesChannelDomainId', $salesChannelDomainId),
                new EqualsFilter('salesChannelDomainId', null),
            ]))
            ->addSorting(new FieldSorting('salesChannelDomainId', FieldSorting::DESCENDING))
            ->setLimit(1);

        /** @var ?RedirectEntity $redirect */
        $redirect = $this->tinectRedirectsRedirectRepository->search($criteria, $context)->first();

        if ($redirect === null) {
            $this->messageBus->dispatch($message);

            return null;
        }

        $message->setId($redirect->getId());
        $this->messageBus->dispatch($message);

        if (!$redirect->active) {
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

        if ($salesChannelContext instanceof SalesChannelContext) {
            return $salesChannelContext;
        }

        $salesChannelId = $request->attributes->getString(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_ID);
        if (empty($salesChannelId)) {
            throw new \RuntimeException('No sales channel id found in request.');
        }

        $languageId = $request->headers->get(PlatformRequest::HEADER_LANGUAGE_ID);
        if (empty($languageId)) {
            throw new \RuntimeException('No language id found in request.');
        }

        return $this->createSalesChannelContext(
            $salesChannelId,
            $languageId
        );
    }

    private function createSalesChannelContext(string $salesChannelId, string $languageId): SalesChannelContext
    {
        return $this->salesChannelContextFactory->create('', $salesChannelId, [SalesChannelContextService::LANGUAGE_ID => $languageId]);
    }
}
