<?php

declare(strict_types=1);

namespace Tinect\Redirects\Message;

use Shopware\Core\Framework\MessageQueue\LowPriorityMessageInterface;

class TinectRedirectUpdateMessage implements LowPriorityMessageInterface
{
    private readonly \DateTimeImmutable $createdAt;

    public function __construct(
        private readonly string $source,
        private readonly ?string $salesChannelDomainId,
        private readonly string $ipAddress,
        private readonly string $userAgent,
        private readonly bool $createRedirect,
        private readonly ?string $id,
    ) {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?string
    {
        return $this->id;
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

    public function isCreateRedirect(): bool
    {
        return $this->createRedirect;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
