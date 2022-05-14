<?php declare(strict_types=1);

namespace Tinect\Redirects\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1652526588Redirect301Default extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1652526588;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement(
            <<<SQL
ALTER TABLE `tinect_redirects_redirect` ALTER `http_code` SET DEFAULT 301;
UPDATE `tinect_redirects_redirect` SET `http_code` = 301;
SQL
        );
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
