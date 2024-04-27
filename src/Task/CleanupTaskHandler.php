<?php

declare(strict_types=1);

namespace Tinect\Redirects\Task;

use Doctrine\DBAL\Connection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Tinect\Redirects\Content\Redirect\RedirectRequestDefinition;

#[AsMessageHandler(handles: CleanupTask::class)]
class CleanupTaskHandler extends ScheduledTaskHandler
{
    public function __construct(
        EntityRepository $scheduledTaskRepository,
        private readonly SystemConfigService $configService,
        private readonly Connection $connection
    ) {
        parent::__construct($scheduledTaskRepository);
    }

    public function run(): void
    {
        $days = $this->configService->getInt('TinectRedirects.config.deleteRequestsAfterDays');

        if ($days === 0) {
            $days = 30;
        }

        $time = new \DateTime();
        $time->modify(sprintf('-%s days', $days));

        $query = $this->connection->createQueryBuilder();

        $query->select('id');
        $query->from(RedirectRequestDefinition::ENTITY_NAME);
        $query->where(
            $query->expr()->lte(
                'created_at',
                $query->createNamedParameter($time->format(Defaults::STORAGE_DATE_TIME_FORMAT))
            )
        );

        $ids = $query->executeQuery()->fetchFirstColumn();

        if (\count($ids) === 0) {
            return;
        }

        $deleteQuery = $this->connection->createQueryBuilder();
        $deleteQuery->delete(RedirectRequestDefinition::ENTITY_NAME);
        $deleteQuery->where('id IN (:ids)');

        foreach (\array_chunk($ids, 1000) as $chunk) {
            $deleteQuery->setParameter('ids', $chunk, Connection::PARAM_STR_ARRAY);
            $deleteQuery->executeQuery();
        }

        $this->connection->executeStatement('UPDATE `tinect_redirects_redirect` SET
            `count` = (SELECT COUNT(*) FROM tinect_redirects_redirect_request WHERE tinect_redirects_redirect_request.tinect_redirects_redirect_id = tinect_redirects_redirect.id)
        ');
    }
}
