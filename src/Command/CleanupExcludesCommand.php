<?php

declare(strict_types=1);

namespace Tinect\Redirects\Command;

use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\Common\RepositoryIterator;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tinect\Redirects\Content\Redirect\RedirectEntity;
use Tinect\Redirects\Services\ExcludedService;

#[AsCommand(name: 'tinect-redirects:excludes-cleanup', description: 'Cleanup existing entries by re-checking them for the exclude patterns.')]
class CleanupExcludesCommand extends Command
{
    public function __construct(
        private readonly EntityRepository $tinectRedirectsRedirectRepository,
        private readonly ExcludedService $excludedService
    ) {
        parent::__construct($this->getName());
    }

    protected function configure(): void
    {
        $this->addOption('dry-run', 'd', null, 'Dry run will not delete any entries but prints ids.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Collecting entries:');

        $context  = new Context(new SystemSource());
        $criteria = (new Criteria())
            ->setLimit(500)
            ->addFilter(new EqualsFilter('active', false))
            ->addAssociation('salesChannelDomain');

        $repositoryIterator = new RepositoryIterator($this->tinectRedirectsRedirectRepository, $context, $criteria);

        $progressBar = new ProgressBar($output, $repositoryIterator->getTotal());
        $progressBar->start();

        $deleteIds = [];
        while (($result = $repositoryIterator->fetch()) !== null) {
            foreach ($result->getEntities() as $redirect) {
                $progressBar->advance();

                if (!$redirect instanceof RedirectEntity) {
                    continue;
                }

                $salesChannelId = $redirect->getSalesChannelDomain()?->getSalesChannelId();

                if ($this->excludedService->isExcluded($redirect->getSource(), $salesChannelId)) {
                    $deleteIds[] = $redirect->getId();
                }
            }
        }

        $progressBar->finish();
        $output->writeln('');

        $countDeletes = \count($deleteIds);

        if ($countDeletes === 0) {
            $output->writeln('No entries to delete.');

            return Command::SUCCESS;
        }

        if ($input->getOption('dry-run')) {
            $output->writeln('Would delete ' . $countDeletes . ' entries');
        } else {
            $output->writeln('Deleting ' . $countDeletes . ' entries:');

            $progressBar = new ProgressBar($output, $countDeletes);
            $progressBar->start();

            $deleteIds = \array_chunk($deleteIds, 1000);
            foreach ($deleteIds as $ids) {
                $this->tinectRedirectsRedirectRepository->delete(\array_map(static fn ($id) => ['id' => $id], $ids), $context);
                $progressBar->advance(\count($ids));
            }

            $progressBar->finish();
            $output->writeln('');
        }

        return Command::SUCCESS;
    }
}
