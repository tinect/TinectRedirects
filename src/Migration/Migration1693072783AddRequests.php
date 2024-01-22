<?php

declare(strict_types=1);

namespace Tinect\Redirects\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1693072783AddRequests extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1693072783;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
            SELECT COUNT(*) AS count 
            FROM information_schema.table_constraints 
            WHERE constraint_schema = DATABASE() 
            AND table_name = 'tinect_redirects_redirect' 
            AND constraint_type = 'PRIMARY KEY'
        SQL;

        $result = $connection->fetchOne($sql);

        if ($result == 0) {
            $connection->executeStatement(<<<SQL
                ALTER TABLE `tinect_redirects_redirect`
                ADD PRIMARY KEY (`id`);
            SQL);
        }

        $connection->executeStatement(
            <<<SQL
        CREATE TABLE IF NOT EXISTS `tinect_redirects_redirect_request` (
        `id` BINARY(16) NOT NULL,
        `tinect_redirects_redirect_id` BINARY(16) NOT NULL,
        `ip_address` VARCHAR(255) NOT NULL,
        `user_agent` VARCHAR(1024) DEFAULT '',
        `created_at` DATETIME(3) NOT NULL,
        `updated_at` DATETIME(3) NULL,

        PRIMARY KEY (`id`),
        KEY `fk.tinect_redirects_redirect.tinect_redirects_redirect_id` (`tinect_redirects_redirect_id`),
        CONSTRAINT `fk.tinect_redirects_redirect.tinect_redirects_redirect_id` FOREIGN KEY (`tinect_redirects_redirect_id`) REFERENCES `tinect_redirects_redirect` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        )
        ENGINE = InnoDB
        DEFAULT CHARSET = utf8mb4
        COLLATE = utf8mb4_unicode_ci;
SQL
        );
    }

    public function updateDestructive(Connection $connection): void {}
}
