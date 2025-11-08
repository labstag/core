<?php

namespace Labstag\Command;

use Labstag\Entity\Page;
use Labstag\Enum\PageEnum;
use Labstag\Repository\PageRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'labstag:add:pages', description: 'Add pages')]
class AddPagesCommand extends Command
{
    public function __construct(
        protected PageRepository $pageRepository,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle   = new SymfonyStyle($input, $output);

        $home = $this->pageRepository->findOneBy(
            [
                'type' => PageEnum::HOME->value,
            ]
        );
        if (!$home instanceof Page) {
            $home = new Page();
            $home->setType(PageEnum::HOME->value);
            $this->pageRepository->save($home);
        }

        foreach (PageEnum::cases() as $case) {
            if ($case->value === PageEnum::HOME->value) {
                continue;
            }

            $page = $this->pageRepository->findOneBy(
                [
                    'type' => $case->value,
                ]
            );
            if (!$page instanceof Page) {
                $page = new Page();
                $page->setType($case->value);
                $page->setPage($home);
                $this->pageRepository->save($page);
            }
        }

        $symfonyStyle->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
