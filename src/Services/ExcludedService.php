<?php

declare(strict_types=1);

namespace Tinect\Redirects\Services;

use Shopware\Core\System\SystemConfig\SystemConfigService;

class ExcludedService
{
    /**
     * Key is sales channel id, value is an array of exclude patterns.
     *
     * @var array<string, array<string>>
     */
    private array $excludes = [];

    public function __construct(
        private readonly SystemConfigService $systemConfigService,
    ) {
    }

    public function isExcluded(string $path, ?string $salesChannelId): bool
    {
        if (!isset($this->excludes[$salesChannelId])) {
            $excludes                        = \array_filter(\explode(PHP_EOL, $this->systemConfigService->getString('TinectRedirects.config.excludes', $salesChannelId)));
            $this->excludes[$salesChannelId] = $excludes;
        }

        $excludes = $this->excludes[$salesChannelId];

        foreach ($excludes as $exclude) {
            try {
                if (\preg_match($exclude, $path)) {
                    return true;
                }
            } catch (\Throwable) {
                // nth, we don't care whether the regex is valid
            }
        }

        return false;
    }
}
