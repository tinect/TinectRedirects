<?php

declare(strict_types=1);

namespace Tinect\Redirects\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1693066141addCountColumn extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1693066141;
    }

    public function update(Connection $connection): void
    {
        if ($this->columnExists($connection, 'tinect_redirects_redirect', 'count')) {
            return;
        }

        $connection->executeStatement(
            'ALTER TABLE `tinect_redirects_redirect` ADD COLUMN `count` bigint(20) NOT NULL DEFAULT 0'
        );
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
