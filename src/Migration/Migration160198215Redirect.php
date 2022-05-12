<?php declare(strict_types=1);

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
        `http_code` INT(3),
        `created_at` DATETIME(3) NOT NULL,
        `updated_at` DATETIME(3) NULL
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
