<?php

namespace Labstag\Command;

use Exception;
use Labstag\Entity\Star;
use Labstag\Repository\StarRepository;
use Labstag\Service\FileService;
use NumberFormatter;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[AsCommand(name: 'labstag:star-add', description: 'Get all star github with npm run star:get')]
class StarAddCommand extends Command
{

    private int $add = 0;

    private int $update = 0;

    public function __construct(
        protected FileService $fileService,
        protected StarRepository $starRepository,
    )
    {
        parent::__construct();
    }

    protected function addOrUpdate(Star $star): void
    {
        if (is_null($star->getId())) {
            ++$this->add;

            return;
        }

        ++$this->update;
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $filename     = 'stars.json';
        $file         = $this->fileService->getFileInAdapter('private', $filename);
        if (!is_file($file)) {
            $symfonyStyle->error('File not found ' . $filename);

            return Command::FAILURE;
        }

        $this->disableAll();

        $json = file_get_contents($file);
        $data = json_decode($json, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            $symfonyStyle->error('Json error');

            return Command::FAILURE;
        }

        $counter  = 0;
        $dataJson = [];
        foreach ($data as $page) {
            $dataJson = array_merge($dataJson, $page);
        }

        $progressBar = new ProgressBar($output, count($dataJson));
        $progressBar->start();
        foreach ($dataJson as $data) {
            if (true != $data['private']) {
                $star = $this->setStar($data);
                $this->addOrUpdate($star);
                $this->starRepository->persist($star);
            }

            ++$counter;
            $progressBar->advance();
            $this->starRepository->flush($counter);
        }

        $this->starRepository->flush();

        $progressBar->finish();

        $symfonyStyle->success('All star added');
        $numberFormatter = new NumberFormatter('fr_FR', NumberFormatter::DECIMAL);
        $symfonyStyle->success(
            sprintf(
                'Added: %s, Updated: %s',
                $numberFormatter->format($this->add),
                $numberFormatter->format($this->update)
            )
        );

        return Command::SUCCESS;
    }

    private function disableAll(): void
    {
        $stars = $this->starRepository->findBy(
            ['enable' => true]
        );
        $counter = 0;
        foreach ($stars as $star) {
            $star->setEnable(false);
            ++$counter;

            $this->starRepository->persist($star);
            $this->starRepository->flush($counter);
        }

        $this->starRepository->flush();
    }

    private function setImage(Star $star, array $data): void
    {
        if (!isset($data['owner']['avatar_url'])) {
            return;
        }

        try {
            $file     = $data['owner']['avatar_url'];
            $tempPath = tempnam(sys_get_temp_dir(), 'star_');
            file_put_contents($tempPath, file_get_contents($file));
            $uploadedFile = new UploadedFile(
                path: $tempPath,
                originalName: basename($tempPath),
                mimeType: mime_content_type($tempPath),
                test: true
            );

            $star->setImgFile($uploadedFile);
        } catch (Exception $exception) {
            echo $exception->getMessage();
        }
    }

    /**
     * @param mixed[] $data
     */
    private function setStar(array $data): Star
    {
        $star = $this->starRepository->findOneBy(
            [
                'repository' => $data['git_url'],
            ]
        );

        if (!$star instanceof Star) {
            $star = new Star();
        }

        $star->setTitle($data['name']);
        $star->setLanguage($data['language']);
        $star->setEnable(true);
        $star->setRepository($data['git_url']);
        $star->setForks($data['forks_count']);
        $star->setUrl($data['html_url']);
        $star->setDescription($data['description'] ?? null);
        $star->setLicense($data['license']['name'] ?? null);
        $star->setStargazers($data['stargazers_count'] ?? 0);
        $star->setWatchers($data['watchers_count'] ?? 0);
        $star->setOwner($data['owner']['login']);
        $this->setImage($star, $data);

        return $star;
    }
}
