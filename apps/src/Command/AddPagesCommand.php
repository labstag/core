<?php

namespace Labstag\Command;

use Labstag\Entity\FormParagraph;
use Labstag\Entity\Page;
use Labstag\Entity\TextParagraph;
use Labstag\Enum\PageEnum;
use Labstag\Repository\PageRepository;
use Labstag\Service\ParagraphService;
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
        protected ParagraphService $paragraphService,
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
                match ($case->value) {
                    'changepassword' => $this->setParagraphsChangePassword($page),
                    'login'          => $this->setParagraphsLogin($page),
                    'lostpassword'   => $this->setParagraphsLostPassword($page),
                    default          => $page->setEnable(false),
                };
                match ($case->value) {
                    'changepassword' => $page->setHide(true),
                    'login'          => $page->setHide(true),
                    'lostpassword'   => $page->setHide(true),
                    default          => $page->setEnable(false),
                };

                $page->setPage($home);
                $this->pageRepository->save($page);
            }
        }

        $symfonyStyle->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }

    private function setParagraphsChangePassword(Page $page): void
    {
        $this->paragraphService->addParagraph($page, 'text');
        $paragraph = $this->paragraphService->addParagraph($page, 'form');
        if (is_null($paragraph) || !$paragraph instanceof FormParagraph) {
            return;
        }

        $paragraph->setSave(true);
        $paragraph->setContent('Formulaire envoyé');
        $paragraph->setForm('change-password');
    }

    private function setParagraphsLogin(Page $page): void
    {
        $this->paragraphService->addParagraph($page, 'text');
        $paragraph = $this->paragraphService->addParagraph($page, 'form');
        if (is_null($paragraph) || !$paragraph instanceof FormParagraph) {
            return;
        }

        $paragraph->setSave(true);
        $paragraph->setContent('Formulaire envoyé');
        $paragraph->setForm('login');
    }

    private function setParagraphsLostPassword(Page $page): void
    {
        $this->paragraphService->addParagraph($page, 'text');
        $paragraph = $this->paragraphService->addParagraph($page, 'form');
        if (is_null($paragraph) || !$paragraph instanceof FormParagraph) {
            return;
        }

        $paragraph->setSave(true);
        $paragraph->setContent('Formulaire envoyé');
        $paragraph->setForm('lost-password');
    }
}
