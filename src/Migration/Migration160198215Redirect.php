<?php

declare(strict_types=1);

namespace Tinect\Redirects\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration160198215Redirect extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 160198215;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement(
            <<<SQL
                        CREATE TABLE IF NOT EXISTS `tinect_redirects_redirect` (
                        `id` BINARY(16) NOT NULL,
                        `source` VARCHAR(255) NOT NULL,
                        `target` VARCHAR(255) NOT NULL,
                        `http_code` INT(3) DEFAULT 302,
                        `sales_channel_domain_id` BINARY(16) NULL,
                        `active` TINYINT(1) NOT NULL DEFAULT 0,
                        `created_at` DATETIME(3) NOT NULL,
                        `updated_at` DATETIME(3) NULL,

                        PRIMARY KEY (`id`),
                        KEY `fk.tinect_redirects_redirect.sales_channel_domain_id` (`sales_channel_domain_id`),
                        CONSTRAINT tinect_redirects_redirect_uc_source UNIQUE (`source`, `sales_channel_domain_id`),
                        CONSTRAINT `fk.tinect_redirects_redirect.sales_channel_domain_id` FOREIGN KEY (`sales_channel_domain_id`) REFERENCES `sales_channel_domain` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
                        )
                        ENGINE = InnoDB
                        DEFAULT CHARSET = utf8mb4
                        COLLATE = utf8mb4_unicode_ci;
                SQL
        );
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
