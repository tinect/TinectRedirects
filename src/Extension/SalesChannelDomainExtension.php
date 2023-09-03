<?php declare(strict_types=1);

namespace Tinect\Redirects\Extension;

use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainDefinition;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Tinect\Redirects\Content\Redirect\RedirectDefinition;

#[AutoconfigureTag('shopware.entity.extension')]
class SalesChannelDomainExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            new OneToManyAssociationField(
                RedirectDefinition::ENTITY_NAME,
                RedirectDefinition::class,
                'sales_channel_domain_id',
                'id'
            )
        );
    }

    public function getDefinitionClass(): string
    {
        return SalesChannelDomainDefinition::class;
    }
}
