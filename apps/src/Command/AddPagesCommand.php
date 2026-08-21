<?php

namespace Labstag\Command;

use Labstag\Entity\FormParagraph;
use Labstag\Entity\Page;
use Labstag\Enum\PageEnum;
use Labstag\Repository\PageRepository;
use Labstag\Service\ParagraphService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'labstag:add:pages', description: 'Add pages')]
class AddPagesCommand
{
    public function __construct(
        protected PageRepository $pageRepository,
        protected ParagraphService $paragraphService,
    )
    {
    }

    public function __invoke(SymfonyStyle $symfonyStyle): int
    {
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
                $this->newPage($home, $case->value);
            }
        }

        $symfonyStyle->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }

    private function newPage(Page $home, string $case): void
    {
        $page = new Page();
        $page->setType($case);
        $this->setTitle($page, $case);
        $this->setData($page, $case);
        $this->setHide($page, $case);
        $page->setPage($home);
        $this->pageRepository->save($page);
    }

    private function setData(Page $page, string $case): void
    {
        match ($case) {
            'changepassword' => $this->setParagraphsChangePassword($page),
            'login'          => $this->setParagraphsLogin($page),
            'lostpassword'   => $this->setParagraphsLostPassword($page),
            'error'          => $page->setEnable(true),
            default          => $page->setEnable(false),
        };
    }

    private function setHide(Page $page, string $case): void
    {
        match ($case) {
            'changepassword' => $page->setHide(true),
            'login'          => $page->setHide(true),
            'lostpassword'   => $page->setHide(true),
            'error'          => $page->setHide(true),
            default          => $page->setHide(false),
        };
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

    private function setTitle(Page $page, string $case): void
    {
        match ($case) {
            'changepassword' => $page->setTitle('Changer le mot de passe'),
            'login'          => $page->setTitle('Se connecter'),
            'lostpassword'   => $page->setTitle('Mot de passe oublié'),
            default          => $page->setTitle($case->value),
        };
    }
}
