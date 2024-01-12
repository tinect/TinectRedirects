<?php

declare(strict_types=1);

namespace Tinect\Redirects\Content\Redirect;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\RemoteAddressField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class RedirectRequestDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'tinect_redirects_redirect_request';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return RedirectRequestCollection::class;
    }

    public function getEntityClass(): string
    {
        return RedirectRequestEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            new FkField('tinect_redirects_redirect_id', 'redirectId', RedirectDefinition::class),
            new RemoteAddressField('ip_address', 'ipAddress'),
            new StringField('user_agent', 'userAgent'),

            new ManyToOneAssociationField('redirect', 'tinect_redirects_redirect_id', RedirectDefinition::class, 'id'),
        ]);
    }
}
