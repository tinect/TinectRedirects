<?php
declare(strict_types=1);

namespace Tinect\Redirects\Content\Redirect;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\AllowHtml;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainDefinition;

class RedirectDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'tinect_redirects_redirect';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return RedirectCollection::class;
    }

    public function getEntityClass(): string
    {
        return RedirectEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            new StringField('source', 'source'),
            new StringField('target', 'target'),
            new IntField('http_code', 'httpCode'),
            new BoolField('active', 'active'),
            new BoolField('hidden', 'hidden'),
            (new LongTextField('comment', 'comment'))->addFlags(new AllowHtml()),
            new FkField('sales_channel_domain_id', 'salesChannelDomainId', SalesChannelDomainDefinition::class),

            new ManyToOneAssociationField('salesChannelDomain', 'sales_channel_domain_id', SalesChannelDomainDefinition::class, 'id', false),
        ]);
    }
}
