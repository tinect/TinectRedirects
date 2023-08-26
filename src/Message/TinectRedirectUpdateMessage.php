<?php declare(strict_types=1);

namespace Tinect\Redirects\Message;

use Shopware\Core\Framework\MessageQueue\AsyncMessageInterface;
use Shopware\Core\Framework\Uuid\Uuid;

class TinectRedirectUpdateMessage implements AsyncMessageInterface
{
    private readonly \DateTimeImmutable $createdAt;

    public function __construct(
        private readonly string $source,
        private readonly ?string $salesChannelDomainId,
        private readonly string $ipAddress,
        private readonly string $userAgent,
        private ?string $id = null,
    ) {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): string
    {
        if ($this->id === null) {
            $this->id = Uuid::randomHex();
        }

        return $this->id;
    }

    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getSalesChannelDomainId(): ?string
    {
        return $this->salesChannelDomainId;
    }

    public function getIpAddress(): string
    {
        return $this->ipAddress;
    }

    public function getUserAgent(): string
    {
        return $this->userAgent;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
