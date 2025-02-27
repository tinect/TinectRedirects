<?php

declare(strict_types=1);

namespace Tinect\Redirects\Message;

use Doctrine\DBAL\Connection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\DataAbstractionLayer\Doctrine\RetryableQuery;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Tinect\Redirects\Event\IsExcludedEvent;
use Tinect\Redirects\Services\RedirectFinderService;

#[AsMessageHandler]
readonly class TinectRedirectUpdateHandler
{
    public function __construct(
        private Connection $connection,
        private EventDispatcherInterface $dispatcher,
        private RedirectFinderService $redirectFinderService
    ) {
    }

    public function __invoke(TinectRedirectUpdateMessage $message): void
    {
        $event = new IsExcludedEvent($message);
        $this->dispatcher->dispatch($event);

        if ($event->isExcluded()) {
            return;
        }

        $redirectId = $message->getId();

        if ($redirectId === null) {
            $redirectId = $this->redirectFinderService->findId($message->getSource(), $message->getSalesChannelDomainId());
        }

        if ($message->canCreateRedirect()) {
            $redirectId = $this->createOrUpdateRedirect($message, $redirectId);
        } elseif ($redirectId !== null) {
            $this->updateRedirectCount($redirectId);
        }

        if ($redirectId !== null) {
            $this->createRedirectRequest($message, $redirectId);
        }
    }

    private function createOrUpdateRedirect(TinectRedirectUpdateMessage $message, ?string $redirectId): string
    {
        $query = new RetryableQuery(
            $this->connection,
            $this->connection->prepare(
                'INSERT INTO `tinect_redirects_redirect` (`id`, `source`, `sales_channel_domain_id`, `created_at`)
                      VALUES (:id, :source, :salesChannelDomainId, :createdAt)
                      ON DUPLICATE KEY UPDATE `count` = count + 1, `updated_at` = NOW()'
            )
        );

        if (!\is_string($redirectId)) {
            $redirectId = Uuid::randomHex();
        }

        $params = [
            'id'                   => Uuid::fromHexToBytes($redirectId),
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

    private function updateRedirectCount(string $redirectId): void
    {
        $query = new RetryableQuery(
            $this->connection,
            $this->connection->prepare(
                'UPDATE `tinect_redirects_redirect` SET `count` = count + 1, `updated_at` = NOW() WHERE `id` = :id'
            )
        );

        $params = [
            'id' => Uuid::fromHexToBytes($redirectId),
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
