<?php declare(strict_types=1);

namespace Tinect\Redirects\Content\Redirect;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainEntity;

class RedirectEntity extends Entity
{
    use EntityIdTrait;

    protected string $source = '';

    protected string $target = '';

    protected int $httpCode = 301;

    protected bool $active = false;

    protected bool $hidden = false;

    protected ?string $comment = null;

    protected ?string $salesChannelDomainId = null;

    protected ?SalesChannelDomainEntity $salesChannelDomain = null;

    protected int $count = 0;

    protected ?RedirectRequestCollection $requests = null;

    public function getSource(): string
    {
        return $this->source;
    }

    public function setSource(string $source): void
    {
        $this->source = $source;
    }

    public function getTarget(): string
    {
        return $this->target;
    }

    public function setTarget(string $target): void
    {
        $this->target = $target;
    }

    public function getHttpCode(): int
    {
        return $this->httpCode;
    }

    public function setHttpCode(int $httpCode): void
    {
        $this->httpCode = $httpCode;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function setHidden(bool $hidden): void
    {
        $this->hidden = $hidden;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): void
    {
        $this->comment = $comment;
    }

    public function getSalesChannelDomainId(): ?string
    {
        return $this->salesChannelDomainId;
    }

    public function setSalesChannelDomainId(?string $salesChannelDomainId): void
    {
        $this->salesChannelDomainId = $salesChannelDomainId;
    }

    public function getSalesChannelDomain(): ?SalesChannelDomainEntity
    {
        return $this->salesChannelDomain;
    }

    public function setSalesChannelDomain(?SalesChannelDomainEntity $salesChannelDomain): void
    {
        $this->salesChannelDomain = $salesChannelDomain;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function setCount(int $count): void
    {
        $this->count = $count;
    }

    public function getRequests(): ?RedirectRequestCollection
    {
        return $this->requests;
    }

    public function setRequests(RedirectRequestCollection $requests): void
    {
        $this->requests = $requests;
    }
}
