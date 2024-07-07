<?php

declare(strict_types=1);

namespace Tinect\Redirects\Message;

use Doctrine\DBAL\Connection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\DataAbstractionLayer\Doctrine\RetryableQuery;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class TinectRedirectUpdateHandler
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    public function __invoke(TinectRedirectUpdateMessage $message): void
    {
        $redirectId = $message->getId();

        if ($message->isCreateRedirect()) {
            $redirectId = $this->createOrUpdateRedirect($message);
        } else {
            $this->updateRedirectCount($message);
        }

        if (\is_string($redirectId)) {
            $this->createRedirectRequest($message, $redirectId);
        }
    }

    private function createOrUpdateRedirect(TinectRedirectUpdateMessage $message): string
    {
        $query = new RetryableQuery(
            $this->connection,
            $this->connection->prepare(
                'INSERT INTO `tinect_redirects_redirect` (`id`, `source`, `sales_channel_domain_id`, `created_at`)
                      VALUES (:id, :source, :salesChannelDomainId, :createdAt)
                      ON DUPLICATE KEY UPDATE `count` = count + 1, `updated_at` = NOW()'
            )
        );

        $params = [
            'id'                   => \is_string($message->getId()) ? Uuid::fromHexToBytes($message->getId()) : Uuid::randomBytes(),
            'source'               => $message->getSource(),
            'salesChannelDomainId' => null,
            'createdAt'            => $message->getCreatedAt()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ];

        if (\is_string($message->getSalesChannelDomainId())) {
            $params['salesChannelDomainId'] = Uuid::fromHexToBytes($message->getSalesChannelDomainId());
        }

        $query->execute($params);

        return Uuid::fromBytesToHex($params['id']);
    }

    private function updateRedirectCount(TinectRedirectUpdateMessage $message): void
    {
        if (!\is_string($message->getId())) {
            return;
        }

        $query = new RetryableQuery(
            $this->connection,
            $this->connection->prepare(
                'UPDATE `tinect_redirects_redirect` SET `count` = count + 1, `updated_at` = NOW() WHERE `id` = :id'
            )
        );

        $params = [
            'id' => Uuid::fromHexToBytes($message->getId()),
        ];

        $query->execute($params);
    }

    private function createRedirectRequest(TinectRedirectUpdateMessage $message, string $redirectId): void
    {
        $query = new RetryableQuery(
            $this->connection,
            $this->connection->prepare(
                'INSERT IGNORE INTO `tinect_redirects_redirect_request` (
                       `id`,
                       `tinect_redirects_redirect_id`,
                       `ip_address`,
                       `user_agent`,
                       `referer`,
                       `created_at`)
                      VALUES (:id, :redirectId, :ipAddress, :userAgent, :referer, :createdAt)'
            )
        );

        $params = [
            'id'         => Uuid::randomBytes(),
            'redirectId' => Uuid::fromHexToBytes($redirectId),
            'ipAddress'  => $message->getIpAddress(),
            'userAgent'  => $message->getUserAgent(),
            'referer'    => $message->getReferer(),
            'createdAt'  => $message->getCreatedAt()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ];

        $query->execute($params);
    }
}
