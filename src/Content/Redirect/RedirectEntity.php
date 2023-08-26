<?php declare(strict_types=1);

namespace Tinect\Redirects\Content\Redirect;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainEntity;

class RedirectEntity extends Entity
{
    use EntityIdTrait;

    public string $source = '';

    public string $target = '';

    public int $httpCode = 301;

    public bool $active = false;

    public bool $hidden = false;

    public ?string $comment = null;

    public ?string $salesChannelDomainId = null;

    public ?SalesChannelDomainEntity $salesChannelDomain = null;

    public int $count = 0;

    public ?RedirectRequestCollection $requests = null;
}
