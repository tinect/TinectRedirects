<?php

declare(strict_types=1);

namespace Tinect\Redirects\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\DataAbstractionLayer\Doctrine\RetryableQuery;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1720360485AddExcludes extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1720360485;
    }

    public function update(Connection $connection): void
    {
        $existingExcludes = $connection->executeQuery(
            'SELECT `id`, `configuration_value` FROM `system_config` WHERE `configuration_key` = :configurationKey',
            ['configurationKey' => 'TinectRedirects.config.excludes']
        )->fetchAllAssociative();

        if (empty($existingExcludes)) {
            return;
        }

        foreach ($existingExcludes as $existingExclude) {
            if (!isset($existingExclude['configuration_value']) || !\is_string($existingExclude['configuration_value'])) {
                continue;
            }

            $configurationValue = json_decode($existingExclude['configuration_value'], true, 512, JSON_THROW_ON_ERROR);

            if (!\is_array($configurationValue) || !isset($configurationValue['_value'])) {
                continue;
            }

            $excludes = $configurationValue['_value'];

            if (!\str_ends_with($excludes, "\n")) {
                $excludes .= "\n";
            }

            $excludes = \str_replace('#.(gz|zip|rar|py|php|ashx)$#i', '#.(gz|zip|rar|py|php|ashx|tgz|tgz|bz2|7z|js|css)$#i', $excludes);

            $excludes .= "#/wp-config#i\n";
            $excludes .= "#/wp-json#i\n";
            $excludes .= "#SELECT%20#\n";

            $query = new RetryableQuery(
                $connection,
                $connection->prepare(
                    'UPDATE `system_config` SET `configuration_value` = :configurationValue WHERE `id` = :id'
                )
            );

            $params = [
                'id'                 => $existingExclude['id'],
                'configurationValue' => json_encode(['_value' => $excludes], JSON_THROW_ON_ERROR),
            ];

            $query->execute($params);
        }
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
