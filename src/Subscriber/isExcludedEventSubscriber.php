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
            IsExcludedEvent::class => ['isExcluded', -1000],
        ];
    }

    public function isExcluded(IsExcludedEvent $event): void
    {
        $message = $event->getUpdateMessage();
        $path = $message->getSource();
        $salesChannelId = $message->getSalesChannelId();

        if (!isset($this->excludes[$salesChannelId])) {
            $excludes                        = \array_filter(\explode(PHP_EOL, $this->systemConfigService->getString('TinectRedirects.config.excludes', $salesChannelId)));
            $this->excludes[$salesChannelId] = $excludes;
        }

        $excludes = $this->excludes[$salesChannelId];

        foreach ($excludes as $exclude) {
            try {
                if (\preg_match($exclude, $path)) {
                    $event->setIsExcluded(true);

                    return;
                }
            } catch (\Throwable) {
                // nth, we don't care whether the regex is valid
            }
        }
    }
}
