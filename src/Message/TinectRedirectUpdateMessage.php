<?php declare(strict_types=1);

namespace Tinect\Redirects\Message;

use Shopware\Core\Framework\MessageQueue\AsyncMessageInterface;

class TinectRedirectUpdateMessage implements AsyncMessageInterface
{
    public function __construct(
        private readonly string $source,
        private readonly ?string $salesChannelDomainId,
        private ?string $id = null,
    ) {
    }

    public function getId(): string
    {
        if ($this->id === null) {
            $this->id = hash('xxh128', $this->source . $this->salesChannelDomainId);
        }

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
}
