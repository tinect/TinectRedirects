<?php declare(strict_types=1);

namespace Tinect\Redirects\Task;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class CleanupTask extends ScheduledTask
{
    final public const NAME = 'tinect.redirects.cleanup';

    public static function getTaskName(): string
    {
        return self::NAME;
    }

    public static function getDefaultInterval(): int
    {
        return 86400;
    }
}
