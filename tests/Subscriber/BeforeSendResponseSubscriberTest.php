<?php

declare(strict_types=1);

namespace Tinect\Redirects\Tests\Subscriber;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Seo\SeoUrlPlaceholderHandlerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Routing\RequestTransformerInterface;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SalesChannel\Context\AbstractSalesChannelContextFactory;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\Test\Stub\MessageBus\CollectingMessageBus;
use Shopware\Core\Test\Stub\SystemConfigService\StaticSystemConfigService;
use Shopware\Storefront\Framework\Routing\RequestTransformer;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Tinect\Redirects\Content\Redirect\RedirectCollection;
use Tinect\Redirects\Content\Redirect\RedirectDefinition;
use Tinect\Redirects\Content\Redirect\RedirectEntity;
use Tinect\Redirects\Message\TinectRedirectUpdateMessage;
use Tinect\Redirects\Services\RedirectFinderService;
use Tinect\Redirects\Subscriber\BeforeSendResponseSubscriber;

#[CoversClass(BeforeSendResponseSubscriber::class)]
class BeforeSendResponseSubscriberTest extends TestCase
{
    private const HOST = 'https://shop.example';

    private CollectingMessageBus $messageBus;

    protected function setUp(): void
    {
        $this->messageBus = new CollectingMessageBus();
    }

    public function testRunsBetweenSalesChannelFilesAndStorefrontNotFoundPage(): void
    {
        $listener = BeforeSendResponseSubscriber::getSubscribedEvents()[KernelEvents::EXCEPTION];

        static::assertSame(['onKernelException', BeforeSendResponseSubscriber::PRIORITY], $listener);
        static::assertLessThan(-90, BeforeSendResponseSubscriber::PRIORITY);
        static::assertGreaterThan(-100, BeforeSendResponseSubscriber::PRIORITY);
    }

    public function testActiveRedirectWithoutQueryString(): void
    {
        $event = $this->dispatch('/foo', ['/foo' => $this->createRedirect('/bar')]);

        $this->assertRedirect($event, self::HOST . '/bar');
    }

    public function testQueryStringIsNotAppendedToExactMatch(): void
    {
        $event = $this->dispatch(
            '/foo?gclid=123',
            ['/foo?gclid=123' => $this->createRedirect('/bar')],
            redirectWithoutQueryString: true
        );

        $this->assertRedirect($event, self::HOST . '/bar');
    }

    public function testFallbackIsDisabledByDefault(): void
    {
        $event = $this->dispatch('/foo?gclid=123', ['/foo' => $this->createRedirect('/bar')]);

        static::assertNull($event->getResponse());
        $this->assertLoggedRequest('/foo?gclid=123', null);
    }

    public function testFallbackToActiveRedirectWithoutQueryString(): void
    {
        $fallbackRedirect = $this->createRedirect('/bar?a=1');

        $event = $this->dispatch(
            '/foo?gclid=123',
            ['/foo' => $fallbackRedirect],
            redirectWithoutQueryString: true
        );

        $this->assertRedirect($event, self::HOST . '/bar?a=1&gclid=123');
        $this->assertLoggedRequest('/foo', $fallbackRedirect->getId());
    }

    public function testFallbackWhenExactMatchIsInactive(): void
    {
        $fallbackRedirect = $this->createRedirect('/bar');

        $event = $this->dispatch(
            '/foo?gclid=123',
            [
                '/foo?gclid=123' => $this->createRedirect('', active: false),
                '/foo' => $fallbackRedirect,
            ],
            redirectWithoutQueryString: true
        );

        $this->assertRedirect($event, self::HOST . '/bar?gclid=123');
        $this->assertLoggedRequest('/foo', $fallbackRedirect->getId());
    }

    public function testInactiveExactMatchIsLoggedWithoutActiveFallback(): void
    {
        $inactiveRedirect = $this->createRedirect('', active: false);

        $event = $this->dispatch(
            '/foo?gclid=123',
            [
                '/foo?gclid=123' => $inactiveRedirect,
                '/foo' => $this->createRedirect('/bar', active: false),
            ],
            redirectWithoutQueryString: true
        );

        static::assertNull($event->getResponse());
        $this->assertLoggedRequest('/foo?gclid=123', $inactiveRedirect->getId());
    }

    public function testNoFallbackToInactiveRedirect(): void
    {
        $event = $this->dispatch(
            '/foo?gclid=123',
            ['/foo' => $this->createRedirect('/bar', active: false)],
            redirectWithoutQueryString: true
        );

        static::assertNull($event->getResponse());
        $this->assertLoggedRequest('/foo?gclid=123', null);
    }

    public function testFallbackToGoneRedirect(): void
    {
        $event = $this->dispatch(
            '/foo?gclid=123',
            ['/foo' => $this->createRedirect('', httpCode: 410)],
            redirectWithoutQueryString: true
        );

        $response = $event->getResponse();
        static::assertNotNull($response);
        static::assertNotInstanceOf(RedirectResponse::class, $response);
        static::assertSame(410, $response->getStatusCode());
    }

    public function testNoFallbackWithoutQueryString(): void
    {
        $event = $this->dispatch('/foo', [], redirectWithoutQueryString: true);

        static::assertNull($event->getResponse());
    }

    public function testTrackingParametersAreNotIgnoredByDefault(): void
    {
        $event = $this->dispatch('/foo?gclid=123', ['/foo' => $this->createRedirect('/bar')]);

        static::assertNull($event->getResponse());
        $this->assertLoggedRequest('/foo?gclid=123', null);
    }

    public function testIgnoreTrackingParameters(): void
    {
        $redirect = $this->createRedirect('/bar');

        $event = $this->dispatch(
            '/foo?gclid=123&utm_source=news',
            ['/foo' => $redirect],
            ignoreTrackingParameters: true
        );

        $this->assertRedirect($event, self::HOST . '/bar?gclid=123&utm_source=news');
        $this->assertLoggedRequest('/foo', $redirect->getId());
    }

    public function testIgnoreTrackingParametersKeepsOtherParameters(): void
    {
        $redirect = $this->createRedirect('/bar');

        $event = $this->dispatch(
            '/foo?page=2&gclid=123',
            ['/foo?page=2' => $redirect],
            ignoreTrackingParameters: true
        );

        $this->assertRedirect($event, self::HOST . '/bar?gclid=123');
        $this->assertLoggedRequest('/foo?page=2', $redirect->getId());
    }

    public function testIgnoreTrackingParametersLogsUnknownRequestWithoutThem(): void
    {
        $event = $this->dispatch(
            '/foo?page=2&gclid=123',
            ['/foo?page=2&gclid=123' => $this->createRedirect('', active: false)],
            ignoreTrackingParameters: true
        );

        static::assertNull($event->getResponse());
        $this->assertLoggedRequest('/foo?page=2', null);
    }

    public function testExactMatchWinsOverIgnoredTrackingParameters(): void
    {
        $redirect = $this->createRedirect('/exact');

        $event = $this->dispatch(
            '/foo?gclid=123',
            [
                '/foo?gclid=123' => $redirect,
                '/foo' => $this->createRedirect('/bar'),
            ],
            ignoreTrackingParameters: true
        );

        $this->assertRedirect($event, self::HOST . '/exact');
        $this->assertLoggedRequest('/foo?gclid=123', $redirect->getId());
    }

    public function testIgnoreTrackingParametersWithFallbackKeepsFullQueryString(): void
    {
        $redirect = $this->createRedirect('/bar');

        $event = $this->dispatch(
            '/foo?page=2&gclid=123',
            ['/foo' => $redirect],
            redirectWithoutQueryString: true,
            ignoreTrackingParameters: true
        );

        $this->assertRedirect($event, self::HOST . '/bar?page=2&gclid=123');
        $this->assertLoggedRequest('/foo', $redirect->getId());
    }

    public function testInvalidUtf8IsScrubbedFromLoggedRequest(): void
    {
        $event = $this->dispatch(
            "/foo\xc3\x28",
            [],
            headers: [
                'User-Agent' => "Mozilla/5.0 \xff\xfe",
                'Referer' => "https://shop.example/\xc3\x28",
            ]
        );

        static::assertNull($event->getResponse());

        $messages = $this->messageBus->getMessages();
        static::assertCount(1, $messages);

        $message = $messages[0]->getMessage();
        static::assertInstanceOf(TinectRedirectUpdateMessage::class, $message);
        static::assertTrue(\mb_check_encoding($message->getSource(), 'UTF-8'));
        static::assertTrue(\mb_check_encoding($message->getUserAgent(), 'UTF-8'));
        static::assertTrue(\mb_check_encoding((string) $message->getReferer(), 'UTF-8'));
    }

    /**
     * @param array<string, RedirectEntity> $redirects
     * @param array<string, string> $headers
     */
    private function dispatch(
        string $path,
        array $redirects,
        bool $redirectWithoutQueryString = false,
        bool $ignoreTrackingParameters = false,
        array $headers = []
    ): ExceptionEvent
    {
        // RedirectFinderService is readonly and cannot be doubled with PHPUnit 10, so its repository is stubbed instead
        $repository = $this->createStub(EntityRepository::class);
        $repository->method('search')->willReturnCallback(
            static function (Criteria $criteria, Context $context) use ($redirects): EntitySearchResult {
                $sourceFilter = $criteria->getFilters()[0];
                static::assertInstanceOf(EqualsFilter::class, $sourceFilter);

                $redirect = $redirects[$sourceFilter->getValue()] ?? null;
                $collection = new RedirectCollection($redirect === null ? [] : [$redirect]);

                // Shopware 6.7 asserts that the entity name matches the API alias of the entities
                return new EntitySearchResult(
                    $redirect?->getApiAlias() ?? RedirectDefinition::ENTITY_NAME,
                    $collection->count(),
                    $collection,
                    null,
                    $criteria,
                    $context
                );
            }
        );

        $seoUrlPlaceholderHandler = $this->createStub(SeoUrlPlaceholderHandlerInterface::class);
        $seoUrlPlaceholderHandler->method('replace')->willReturnArgument(0);

        $subscriber = new BeforeSendResponseSubscriber(
            $seoUrlPlaceholderHandler,
            $this->createStub(AbstractSalesChannelContextFactory::class),
            new StaticSystemConfigService([
                'TinectRedirects.config.redirectWithoutQueryString' => $redirectWithoutQueryString,
                'TinectRedirects.config.ignoreTrackingParameters' => $ignoreTrackingParameters,
            ]),
            $this->messageBus,
            $this->createStub(RequestTransformerInterface::class),
            new RedirectFinderService($repository),
            ['gclid', 'utm_source']
        );

        $request = Request::create(self::HOST . $path);
        $request->headers->add($headers);
        $request->attributes->set(RequestTransformer::ORIGINAL_REQUEST_URI, $path);
        $request->attributes->set(RequestTransformer::SALES_CHANNEL_ABSOLUTE_BASE_URL, self::HOST);
        $request->attributes->set(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_CONTEXT_OBJECT, $this->createStub(SalesChannelContext::class));

        $event = new ExceptionEvent(
            $this->createStub(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            new NotFoundHttpException()
        );

        $subscriber->onKernelException($event);

        return $event;
    }

    private function createRedirect(string $target, bool $active = true, int $httpCode = 301): RedirectEntity
    {
        $redirect = new RedirectEntity();
        $redirect->setId(Uuid::randomHex());
        $redirect->setTarget($target);
        $redirect->setActive($active);
        $redirect->setHttpCode($httpCode);

        return $redirect;
    }

    private function assertRedirect(ExceptionEvent $event, string $expectedTarget): void
    {
        $response = $event->getResponse();

        static::assertInstanceOf(RedirectResponse::class, $response);
        static::assertSame($expectedTarget, $response->getTargetUrl());
        static::assertSame(301, $response->getStatusCode());
    }

    private function assertLoggedRequest(string $expectedSource, ?string $expectedId): void
    {
        $messages = $this->messageBus->getMessages();
        static::assertCount(1, $messages);

        $message = $messages[0]->getMessage();
        static::assertInstanceOf(TinectRedirectUpdateMessage::class, $message);
        static::assertSame($expectedSource, $message->getSource());
        static::assertSame($expectedId, $message->getId());
    }
}
