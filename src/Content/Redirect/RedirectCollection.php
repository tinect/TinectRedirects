<?php

declare(strict_types=1);

namespace Tinect\Redirects\Content\Redirect;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @extends EntityCollection<RedirectEntity>
 */
class RedirectCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return RedirectEntity::class;
    }
}
