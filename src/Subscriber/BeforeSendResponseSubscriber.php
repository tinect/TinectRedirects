<?php

declare(strict_types=1);

namespace Tinect\Redirects\Subscriber;

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
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Framework\Routing\RequestTransformer;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\MessageBusInterface;
use Tinect\Redirects\Content\Redirect\RedirectEntity;
use Tinect\Redirects\Message\TinectRedirectUpdateMessage;

class BeforeSendResponseSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly EntityRepository $tinectRedirectsRedirectRepository,
        private readonly SeoUrlPlaceholderHandlerInterface $seoUrlPlaceholderHandler,
        #[Autowire(service: SalesChannelContextFactory::class)]
        private readonly AbstractSalesChannelContextFactory $salesChannelContextFactory,
        private readonly SystemConfigService $systemConfigService,
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

        $exception = $event->getThrowable();

        $is404StatusCode = $exception instanceof HttpException && $exception->getStatusCode() === Response::HTTP_NOT_FOUND;

        if (!$is404StatusCode) {
            return;
        }

        $response = $this->handleRequest($request);

        if ($response instanceof Response) {
            $event->setResponse($response);
        }
    }

    private function handleRequest(Request $request): ?Response
    {
        $path = $request->attributes->getString(RequestTransformer::ORIGINAL_REQUEST_URI);

        if ($path === '') {
            return null;
        }

        $salesChannelBaseUrl = $request->attributes->getString(RequestTransformer::SALES_CHANNEL_BASE_URL);

        if ($salesChannelBaseUrl !== ''
            && \str_starts_with($path, $salesChannelBaseUrl . '/')) {
            $path = \substr($path, \strlen($salesChannelBaseUrl));
        }

        $salesChannelDomainId = $request->attributes->get(SalesChannelRequest::ATTRIBUTE_DOMAIN_ID);

        if (!\is_string($salesChannelDomainId) || empty($salesChannelDomainId)) {
            $salesChannelDomainId = null;
        }

        $context  = new Context(new SystemSource());
        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter('source', $path))
            ->addFilter(
                new MultiFilter(MultiFilter::CONNECTION_OR, [
                    new EqualsFilter('salesChannelDomainId', $salesChannelDomainId),
                    new EqualsFilter('salesChannelDomainId', null),
                ])
            )
            ->addSorting(new FieldSorting('salesChannelDomainId', FieldSorting::DESCENDING))
            ->setLimit(1);

        /** @var ?RedirectEntity $redirect */
        $redirect = $this->tinectRedirectsRedirectRepository->search($criteria, $context)->first();

        $message = new TinectRedirectUpdateMessage(
            source: $path,
            salesChannelDomainId: $salesChannelDomainId,
            ipAddress: $this->getIpAddress($request),
            userAgent: $request->headers->get('User-Agent') ?? '',
            createRedirect: $this->canCreateRedirect($path, $salesChannelDomainId),
            id: $redirect?->getId(),
            referer: $request->headers->get('referer'),
        );

        $this->messageBus->dispatch($message);

        if ($redirect === null || !$redirect->isActive()) {
            return null;
        }

        $httpCode = $redirect->getHttpCode();

        if ($httpCode === Response::HTTP_GONE) {
            return new Response('', $httpCode);
        }

        $targetURL = $redirect->getTarget();

        $host = $request->attributes->getString(RequestTransformer::SALES_CHANNEL_ABSOLUTE_BASE_URL)
            . $salesChannelBaseUrl;

        $salesChannelContext = $this->getSalesChannelContext($request);

        $targetURL = $this->seoUrlPlaceholderHandler->replace($targetURL, $host, $salesChannelContext);

        if (!parse_url($targetURL, PHP_URL_SCHEME)) {
            $targetURL = $host . $targetURL;
        }

        return new RedirectResponse($targetURL, $httpCode);
    }

    private function getSalesChannelContext(Request $request): SalesChannelContext
    {
        $salesChannelContext = $request->attributes->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_CONTEXT_OBJECT);

        if ($salesChannelContext instanceof SalesChannelContext) {
            return $salesChannelContext;
        }

        $salesChannelId = $request->attributes->getString(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_ID);

        if ($salesChannelId === '') {
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
        return $this->salesChannelContextFactory->create(
            '',
            $salesChannelId,
            [SalesChannelContextService::LANGUAGE_ID => $languageId]
        );
    }

    private function getIpAddress(Request $request): string
    {
        $salesChannelId = $request->attributes->getString(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_ID);

        if ($salesChannelId === '') {
            $salesChannelId = null;
        }

        if (!$this->canSaveIpAddress($salesChannelId)) {
            return '';
        }

        return $request->getClientIp() ?? '';
    }

    private function canSaveIpAddress(?string $salesChannelId): bool
    {
        return $this->systemConfigService->getBool('TinectRedirects.config.saveIpAddresses', $salesChannelId);
    }

    private function canCreateRedirect(string $path, ?string $salesChannelId): bool
    {
        if (!$this->systemConfigService->getBool('TinectRedirects.config.createNewRedirects', $salesChannelId)) {
            return false;
        }

        return $this->canCreateRedirectByExcludes($path, $salesChannelId);
    }

    private function canCreateRedirectByExcludes(string $path, ?string $salesChannelId): bool
    {
        $excludes = \explode(PHP_EOL, $this->systemConfigService->getString('TinectRedirects.config.excludes', $salesChannelId));

        foreach ($excludes as $exclude) {
            try {
                if (\preg_match($exclude, $path)) {
                    return false;
                }
            } catch (\Throwable) {
                // nth, we don't care whether the regex is valid
            }
        }

        return true;
    }
}
