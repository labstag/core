<?php

declare(strict_types=1);

namespace Labstag\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Doctrine\ORM\EntityManagerInterface;
use Labstag\Service\TrashStatsService;
use Labstag\Controller\Admin\DashboardController;

#[AsCommand(name: 'dashboard:cache:warmup', description: 'Pre-warm the admin dashboard cache')]
class DashboardCacheWarmupCommand extends Command
{
    public function __construct(
        private CacheInterface $cache,
        private EntityManagerInterface $entityManager,
        private TrashStatsService $trashStatsService,
        #[\Symfony\Component\DependencyInjection\Attribute\AutowireIterator('labstag.admincontroller')]
        private iterable $crudControllers,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Warming up dashboard cache...');

        $entities = [];
        foreach ($this->crudControllers as $controller) {
            $fqcn = $controller::getEntityFqcn();
            $label = $controller::getEntityLabel();
            $entities[$label] = [$fqcn, $controller::class];
        }

        $urlGenerator = null; // not needed for cache key, but stats need urls
        // We'll compute stats without urls (urls generated at render time)
        $stats = [];
        foreach ($entities as $label => [$entityFqcn, $controllerFqcn]) {
            if (str_contains($label, 'Tag') || str_contains($label, 'Category')) {
                continue;
            }
            $stats[] = [
                'label' => $label,
                'count' => $this->entityManager->getRepository($entityFqcn)->count([]),
            ];
        }

        $this->cache->get('admin_dashboard_data', function ($item) use ($stats) {
            $item->expiresAfter(300);
            return ['stats' => $stats, 'trash' => []];
        });

        $output->writeln('Done.');

        return Command::SUCCESS;
    }
}
