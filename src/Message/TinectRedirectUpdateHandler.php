<?php declare(strict_types=1);

namespace Tinect\Redirects\Message;

use Doctrine\DBAL\Connection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\DataAbstractionLayer\Doctrine\RetryableQuery;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(priority: 100)]
class TinectRedirectUpdateHandler
{
    public function __construct(
        private readonly Connection $connection,
        private readonly SystemConfigService $systemConfigService,
    ) {
    }

    public function __invoke(TinectRedirectUpdateMessage $message): void
    {
        $query = new RetryableQuery(
            $this->connection,
            $this->connection->prepare('INSERT INTO `tinect_redirects_redirect` (`id`, `source`, `sales_channel_domain_id`, `created_at`)
                      VALUES (:id, :source, :salesChannelDomainId, :createdAt)
                      ON DUPLICATE KEY UPDATE `count` = count + 1')
        );

        $params = [
            'id' => Uuid::fromHexToBytes($message->getId()),
            'source' => $message->getSource(),
            'salesChannelDomainId' => null,
            'createdAt' => $message->getCreatedAt()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ];

        if (\is_string($message->getSalesChannelDomainId())) {
            $params['salesChannelDomainId'] = Uuid::fromHexToBytes($message->getSalesChannelDomainId());
        }

        $query->execute($params);

        $query = $this->connection->prepare('SELECT `id` FROM `tinect_redirects_redirect` WHERE `source`=:source AND `sales_channel_domain_id`=:salesChannelDomainId');
        $id = $query->executeQuery([
            'source' => $message->getSource(),
            'salesChannelDomainId' => $params['salesChannelDomainId'],
        ])->fetchOne();

        $query = new RetryableQuery(
            $this->connection,
            $this->connection->prepare('INSERT INTO `tinect_redirects_redirect_request` (`id`, `tinect_redirects_redirect_id`, `ip_address`, `user_agent`, `created_at`)
                      VALUES (:id, :redirectId, :ipAddress, :userAgent, :createdAt)')
        );

        $params = [
            'id' => Uuid::randomBytes(),
            'redirectId' => $id,
            'ipAddress' => $this->canSaveIpAddress() ? $message->getIpAddress() : '',
            'userAgent' => $message->getUserAgent(),
            'createdAt' => $message->getCreatedAt()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ];

        $query->execute($params);
    }

    private function canSaveIpAddress(): bool
    {
        return $this->systemConfigService->getBool('TinectRedirects.config.saveIpAddresses');
    }
}
