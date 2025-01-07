<?php

namespace Labstag\Command;

use Labstag\Entity\Template;
use Labstag\Repository\TemplateRepository;
use Labstag\Service\EmailService;
use NumberFormatter;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'labstag:email:template-add',
    description: '',
)]
class EmailTemplateAddCommand extends Command
{

    private int $add = 0;

    private int $update = 0;

    public function __construct(
        protected EmailService $emailService,
        protected TemplateRepository $templateRepository
    )
    {
        parent::__construct();
    }

    protected function addOrUpdate(?Template $template): void
    {
        if (is_null($template->getId())) {
            ++$this->add;

            return;
        }

        ++$this->update;
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);

        $templates   = $this->emailService->all();
        $counter     = 0;
        $progressBar = new ProgressBar($output, is_countable($templates) ? count($templates) : 0);
        $progressBar->start();
        foreach ($templates as $row) {
            $template = $this->templateRepository->findOneBy(
                ['code' => $row->getType()]
            );
            $this->addOrUpdate($template);
            if (!$template instanceof Template) {
                $template = new Template();
                $template->setCode($row->getType());
                $template->setText($row->setText());
                $template->setHtml($row->setHtml());
                $template->setTitle($row->getName());
                $this->templateRepository->persist($template);
            }

            ++$counter;
            $this->templateRepository->flush($counter);
            $progressBar->advance();
        }

        $progressBar->finish();
        $symfonyStyle->newLine();

        $this->templateRepository->flush();

        $numberFormatter = new NumberFormatter('fr_FR', NumberFormatter::DECIMAL);
        $symfonyStyle->success(
            sprintf(
                'Added: %d',
                $numberFormatter->format($this->add)
            )
        );

        return Command::SUCCESS;
    }
}
