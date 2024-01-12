<?php

declare(strict_types=1);

namespace Tinect\Redirects\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1652791720AddFields extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1652791720;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement(
            <<<SQL
    ALTER TABLE `tinect_redirects_redirect`
        ADD COLUMN `hidden` TINYINT(1) NOT NULL DEFAULT 0 AFTER `sales_channel_domain_id`;
    ALTER TABLE `tinect_redirects_redirect`
        ADD COLUMN `comment` LONGTEXT NULL AFTER `hidden`;
SQL
        );
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
