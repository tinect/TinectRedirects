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
        $criteria = $this->getCriteria($path, $salesChannelDomainId);

        $redirect = $this->tinectRedirectsRedirectRepository->search($criteria, $context)->first();

        if ($redirect instanceof RedirectEntity) {
            return $redirect;
        }

        return null;
    }

    public function findId(string $path, ?string $salesChannelDomainId): ?string
    {
        $context  = new Context(new SystemSource());
        $criteria = $this->getCriteria($path, $salesChannelDomainId);

        return $this->tinectRedirectsRedirectRepository->searchIds($criteria, $context)->firstId();
    }

    private function getCriteria(string $path, ?string $salesChannelDomainId): Criteria
    {
        return (new Criteria())
            ->addFilter(new EqualsFilter('source', $path))
            ->addFilter(
                new MultiFilter(MultiFilter::CONNECTION_OR, [
                    new EqualsFilter('salesChannelDomainId', $salesChannelDomainId),
                    new EqualsFilter('salesChannelDomainId', null),
                ])
            )
            ->addSorting(new FieldSorting('salesChannelDomainId', FieldSorting::DESCENDING))
            ->setLimit(1);
    }
}
