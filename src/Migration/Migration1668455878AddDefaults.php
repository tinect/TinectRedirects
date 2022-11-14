<?php declare(strict_types=1);

namespace Tinect\Redirects\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1668455878AddDefaults extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1668455878;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement(
            <<<SQL
ALTER TABLE `tinect_redirects_redirect` MODIFY `source` varchar(512) DEFAULT '';
ALTER TABLE `tinect_redirects_redirect` MODIFY `target` varchar(512) DEFAULT '';
SQL
        );
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
