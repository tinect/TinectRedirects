<?php

declare(strict_types=1);

namespace Tinect\Redirects\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;
use Tinect\Redirects\Content\Redirect\RedirectDefinition;

class Migration1738788990ImportExportTechnicalName extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1738788990;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement(
            'UPDATE import_export_profile SET technical_name = :technicalName WHERE source_entity = :sourceEntity AND system_default = 1',
            [
                'technicalName' => 'default_' . RedirectDefinition::ENTITY_NAME,
                'sourceEntity' => RedirectDefinition::ENTITY_NAME,
            ]
        );
    }
}
