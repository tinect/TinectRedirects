<?php declare(strict_types=1);

namespace Tinect\Redirects\Content\Redirect;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class RedirectRequestEntity extends Entity
{
    use EntityIdTrait;

    public ?string $redirectId = null;

    public ?RedirectEntity $redirect = null;

    public string $ipAddress = '';

    public string $userAgent = '';
}
