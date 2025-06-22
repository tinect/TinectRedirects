<?php

declare(strict_types=1);

namespace Tinect\Redirects\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Uuid\Uuid;
use Tinect\Redirects\Content\Redirect\RedirectDefinition;

class Migration1615031757ImportExportProfile extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1615031757;
    }

    public function update(Connection $connection): void
    {
        foreach ($this->getProfiles() as $profile) {
            $profile['id']             = Uuid::randomBytes();
            $profile['system_default'] = 1;
            $profile['file_type']      = 'text/csv';
            $profile['delimiter']      = ';';
            $profile['enclosure']      = '"';
            $profile['mapping']        = json_encode($profile['mapping'], \JSON_THROW_ON_ERROR);
            $profile['created_at']     = (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT);

            $name = $profile['name'];
            unset($profile['name']);

            $connection->insert('import_export_profile', $profile);

            $translation = [
                'import_export_profile_id' => $profile['id'],
                'language_id'              => Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM),
                'label'                    => $name,
                'created_at'               => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ];
            $connection->insert('import_export_profile_translation', $translation);
        }
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }

    /**
     * @return array<int, array{
     *     name: string,
     *     source_entity: string,
     *     mapping: array<int, array{'key': string, 'mappedKey': string}>
     *     }>
     */
    private function getProfiles(): array
    {
        return [
            [
                'name'          => 'Default redirect',
                'source_entity' => RedirectDefinition::ENTITY_NAME,
                'technical_name' => 'default_' . RedirectDefinition::ENTITY_NAME,
                'mapping'       => [
                    ['key' => 'id', 'mappedKey' => 'id'],
                    ['key' => 'httpCode', 'mappedKey' => 'http_code'],
                    ['key' => 'source', 'mappedKey' => 'source'],
                    ['key' => 'target', 'mappedKey' => 'target'],
                ],
            ],
        ];
    }
}
