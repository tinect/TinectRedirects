<?php

declare(strict_types=1);

namespace Tinect\Redirects\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1710072639AddReferer extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1710072639;
    }

    public function update(Connection $connection): void
    {
        if ($this->columnExists($connection, 'tinect_redirects_redirect_request', 'referer')) {
            return;
        }

        $sql = <<<SQL
            ALTER TABLE `tinect_redirects_redirect_request`
            ADD COLUMN `referer` varchar(1024) NULL;
            SQL;

        $connection->executeStatement($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
