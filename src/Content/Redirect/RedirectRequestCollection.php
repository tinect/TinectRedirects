<?php declare(strict_types=1);

namespace Tinect\Redirects\Content\Redirect;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @extends EntityCollection<RedirectRequestEntity>
 */
class RedirectRequestCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return RedirectRequestEntity::class;
    }
}
