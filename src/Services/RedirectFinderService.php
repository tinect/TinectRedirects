<?php

declare(strict_types=1);

namespace Tinect\Redirects\Services;

use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Tinect\Redirects\Content\Redirect\RedirectEntity;

readonly class RedirectFinderService
{
    public function __construct(
        private EntityRepository $tinectRedirectsRedirectRepository,
    ) {
    }

    public function find(string $path, ?string $salesChannelDomainId): ?RedirectEntity
    {
        $context  = new Context(new SystemSource());
        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter('source', $path))
            ->addFilter(
                new MultiFilter(MultiFilter::CONNECTION_OR, [
                    new EqualsFilter('salesChannelDomainId', $salesChannelDomainId),
                    new EqualsFilter('salesChannelDomainId', null),
                ])
            )
            ->addSorting(new FieldSorting('salesChannelDomainId', FieldSorting::DESCENDING))
            ->setLimit(1);

        $redirect = $this->tinectRedirectsRedirectRepository->search($criteria, $context)->first();

        if ($redirect instanceof RedirectEntity) {
            return $redirect;
        }

        return null;
    }
}
