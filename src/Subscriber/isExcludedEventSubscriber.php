<?php

namespace Tinect\Redirects\Subscriber;

use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tinect\Redirects\Event\IsExcludedEvent;

class isExcludedEventSubscriber implements EventSubscriberInterface
{
    /**
     * Key is sales channel id, value is an array of exclude patterns.
     *
     * @var array<string, array<string>>
     */
    private array $excludes = [];

    public function __construct(
        private readonly SystemConfigService $systemConfigService
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            IsExcludedEvent::class => [
                ['isExcluded', -1000],
                ['isBadUserAgent', -1002]
            ],
        ];
    }

    public function isBadUserAgent(IsExcludedEvent $event): void
    {
        if ($event->isExcluded()) {
            return;
        }

        $badUserAgentsPath = dirname(__DIR__) . '/Resources/lists/bad-user-agents.txt';

        if (!\is_file($badUserAgentsPath)) {
            throw new \RuntimeException('Bad user agents file not found at ' . $badUserAgentsPath);
        }

        $badUserAgents = \file_get_contents($badUserAgentsPath);
        \assert(\is_string($badUserAgents));

        $badUserAgents = \trim($badUserAgents);
        $badUserAgents = \preg_quote($badUserAgents, '/');
        $badUserAgents = \explode(PHP_EOL, $badUserAgents);
        $badUserAgents = \implode('|', $badUserAgents);

        $message = $event->getUpdateMessage();
        $userAgent = $message->getUserAgent();

        $badBotsRegex = '/' . $badUserAgents . '/i';

        if (\preg_match($badBotsRegex, $userAgent) === 1) {
            $event->setIsExcluded(true);
        }
    }

    public function isExcluded(IsExcludedEvent $event): void
    {
        if ($event->isExcluded()) {
            return;
        }

        $message = $event->getUpdateMessage();
        $path = $message->getSource();
        $salesChannelId = $message->getSalesChannelId();

        if (!isset($this->excludes[$salesChannelId])) {
            $excludes                        = \array_filter(
                \explode(PHP_EOL, $this->systemConfigService->getString('TinectRedirects.config.excludes', $salesChannelId)),
                static fn ($exclude) => $exclude !== '');
            $this->excludes[$salesChannelId] = $excludes;
        }

        $excludes = $this->excludes[$salesChannelId];

        foreach ($excludes as $exclude) {
            try {
                if (\preg_match($exclude, $path) === 1) {
                    $event->setIsExcluded(true);

                    return;
                }
            } catch (\Throwable) {
                // nth, we don't care whether the regex is valid
            }
        }
    }
}
