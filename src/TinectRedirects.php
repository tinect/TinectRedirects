<?php

declare(strict_types=1);

namespace Tinect\Redirects;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;

class TinectRedirects extends Plugin
{
    public function uninstall(UninstallContext $uninstallContext): void
    {
        parent::uninstall($uninstallContext);

        if ($uninstallContext->keepUserData()) {
            return;
        }

        if ($this->container === null) {
            return;
        }

        /** @var Connection $connection */
        $connection = $this->container->get(Connection::class);
        $connection->executeStatement('DROP TABLE IF EXISTS `tinect_redirects_redirect_request`;');
        $connection->executeStatement('DROP TABLE IF EXISTS `tinect_redirects_redirect`;');
    }
}
