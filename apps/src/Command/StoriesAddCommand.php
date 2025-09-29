<?php

namespace Labstag\Command;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Labstag\Entity\Category;
use Labstag\Entity\Chapter;
use Labstag\Entity\Meta;
use Labstag\Entity\Story;
use Labstag\Entity\Tag;
use Labstag\Entity\User;
use Labstag\Repository\CategoryRepository;
use Labstag\Repository\ChapterRepository;
use Labstag\Repository\StoryRepository;
use Labstag\Repository\TagRepository;
use Labstag\Repository\UserRepository;
use Labstag\Service\ParagraphService;
use Labstag\Service\UserService;
use Labstag\Service\WorkflowService;
use NumberFormatter;
use PDO;
use PDOException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'labstag:stories-add',
    description: 'Lit et affiche le contenu de la table histoires depuis le fichier SQLite',
)]
class StoriesAddCommand extends Command
{

    protected array $users = [];

    public function __construct(
        protected StoryRepository $storyRepository,
        protected ParagraphService $paragraphService,
        protected ChapterRepository $chapterRepository,
        protected EntityManagerInterface $entityManager,
        protected UserRepository $userRepository,
        protected CategoryRepository $categoryRepository,
        protected UserService $userService,
        protected TagRepository $tagRepository,
        protected WorkflowService $workflowService,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(
            'option',
            InputArgument::REQUIRED,
            'Si fourni, traite seulement les utilisateurs/auteurs sans créer les histoires',
            null
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);

        // Chemin vers le fichier SQLite
        $sqliteFile = __DIR__ . '/../../private/stories.sqlite';

        $numberFormatter = new NumberFormatter('fr_FR', NumberFormatter::DECIMAL);
        if (!file_exists($sqliteFile)) {
            $symfonyStyle->error("Le fichier SQLite stories.sqlite n'existe pas.");

            return Command::FAILURE;
        }

        try {
            // Ouvrir la connexion SQLite
            $pdo = new PDO('sqlite:' . $sqliteFile);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $symfonyStyle->success('Connexion SQLite établie avec succès !');

            // Vérifier si la table 'histoires' existe
            $stmt = $pdo->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name='histoires'");
            $stmt->execute();
            $tableExists = $stmt->fetch();

            if (!$tableExists) {
                $symfonyStyle->error('La table "histoires" n\'existe pas dans la base de données SQLite.');

                return Command::FAILURE;
            }

            // Lire toutes les histoires
            $symfonyStyle->section('Lecture de la table "histoires" :');

            // D'abord, compter le nombre total d'histoires
            $stmt  = $pdo->query('SELECT COUNT(*) as total FROM histoires');
            $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            $symfonyStyle->note(sprintf("Nombre total d'histoires : %s", $numberFormatter->format($total)));

            if (0 == $total) {
                $symfonyStyle->warning('La table "histoires" est vide.');

                return Command::FAILURE;
            }

            $option = $input->getArgument('option');
            match ($option) {
                'delete'     => $this->deleteAll(),
                'authors'    => $this->addUsers($symfonyStyle, $pdo),
                'categories' => $this->addCategories($symfonyStyle, $pdo),
                'stories'    => $this->addStoryByUsers($symfonyStyle, $pdo),
                'tags'       => $this->addTags($symfonyStyle, $pdo),
                default      => $symfonyStyle->error(
                    'Option invalide. Utilisez "authors", "categories", "stories", "tags" ou "delete".'
                ),
            };

            return Command::SUCCESS;
        } catch (PDOException $pdoException) {
            $symfonyStyle->error("Erreur lors de l'ouverture du fichier SQLite : " . $pdoException->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function addCategories(SymfonyStyle $symfonyStyle, PDO $pdo): void
    {
        $stmt        = $pdo->query(
            'SELECT categorie, count(*) FROM histoires WHERE categorie IS NOT NULL GROUP BY categorie'
        );
        $data        = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $progressBar = new ProgressBar($symfonyStyle, count($data));
        $progressBar->start();

        $counter     = 0;
        foreach ($data as $row) {
            $category = $this->createOrGetCategory($row['categorie']);
            ++$counter;

            $progressBar->advance();
            if (!$category instanceof Category) {
                continue;
            }

            $this->categoryRepository->persist($category);
            $this->categoryRepository->flush($counter);
        }

        $progressBar->finish();
        $this->categoryRepository->flush();
    }

    private function addCategoryTagStory(Story $story, array $chapters): void
    {
        foreach ($chapters as $chapter) {
            $tags           = explode(',', (string) $chapter['tags']);
            $titleCategorie = $chapter['categorie'];
            $category       = $this->createOrGetCategory($titleCategorie);
            $story->addCategory($category);
            foreach ($tags as $tag) {
                $tag       = trim($tag);
                $tagEntity = $this->createOrGetTag($tag, 'story');
                if ($tagEntity instanceof Tag) {
                    $story->addTag($tagEntity);
                }
            }
        }
    }

    private function addChapter(Story $story, array $data, string $chapitre): void
    {
        $chapter = $this->chapterRepository->findOneBy(
            [
                'refstory' => $story,
                'title'    => $chapitre,
            ]
        );
        if (!$chapter instanceof Chapter) {
            $chapter = new Chapter();
            $meta    = new Meta();
            $chapter->setMeta($meta);
            $chapter->setEnable(true);
            $chapter->setRefStory($story);
            $chapter->setTitle($chapitre);
        }

        $date = DateTime::createFromFormat('d-m-Y H:i', $data['date_publication']);
        if ($date instanceof DateTime) {
            $chapter->setCreatedAt($date);
            $chapter->setUpdatedAt($date);
        }

        $tags = explode(',', (string) $data['tags']);
        foreach ($tags as $tag) {
            $tag       = trim($tag);
            $tagEntity = $this->createOrGetTag($tag, 'chapter');
            if ($tagEntity instanceof Tag) {
                $chapter->addTag($tagEntity);
            }
        }

        $paragraph     = $this->getParagraphTextChapter($chapter);
        $remplacements = [
            "\r\n" => '<br>',
            // Windows (CRLF)
            "\n"   => '<br>',
            // Unix (LF)
            "\r"   => '<br>',
            // Mac ancien (CR)
            "\t"   => '&nbsp;&nbsp;&nbsp;&nbsp;',
            // tabulations
        ];

        $paragraph->setContent(strtr($data['contenu'], $remplacements));

        $this->chapterRepository->persist($chapter);
        $this->chapterRepository->flush();
    }

    private function addStoryByUser(SymfonyStyle $symfonyStyle, PDO $pdo, User $user): void
    {
        $auteur = $user->getUsername();
        $stmt   = $pdo->prepare(
            "SELECT * FROM histoires WHERE auteur = :auteur ORDER BY strftime('%Y-%m-%d %H:%M', substr(date_publication, 7, 4) || '-' || substr(date_publication, 4, 2) || '-' || substr(date_publication, 1, 2) || ' ' || substr(date_publication, 12, 5))"
        );
        $stmt->execute(
            ['auteur' => $auteur]
        );
        $histoires = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($histoires)) {
            return;
        }

        $data            = $this->groupByStory($histoires);
        $progressBar     = new ProgressBar($symfonyStyle, count($data));
        $numberFormatter = new NumberFormatter('fr_FR', NumberFormatter::DECIMAL);
        $symfonyStyle->section(
            sprintf(
                "Traitement des histoires pour l'auteur : %s (Total : %s)",
                $auteur,
                $numberFormatter->format(count($data))
            )
        );
        $progressBar->start();
        $counter = 0;
        foreach ($data as $title => $chapters) {
            $this->createStory($title, $chapters, $user);
            ++$counter;
            $progressBar->advance();
        }

        $this->entityManager->flush();
        $progressBar->finish();

        $symfonyStyle->newLine(2);
    }

    private function addStoryByUsers(SymfonyStyle $symfonyStyle, PDO $pdo): void
    {
        $users = $this->userRepository->findAll();
        foreach ($users as $user) {
            $this->addStoryByUser($symfonyStyle, $pdo, $user);
        }
    }

    private function addTags(SymfonyStyle $symfonyStyle, PDO $pdo): void
    {
        $stmt        = $pdo->query('SELECT tags, count(*) FROM histoires WHERE tags IS NOT NULL GROUP BY tags');
        $database    = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $counter     = 0;
        $data        = [];
        foreach ($database as $row) {
            $tags = explode(',', (string) $row['tags']);
            foreach ($tags as $tag) {
                $tag        = trim($tag);
                $data[$tag] = 1;
            }
        }

        $progressBar = new ProgressBar($symfonyStyle, count($data));
        $progressBar->start();
        foreach (array_keys($data) as $tag) {
            $tagtype = [
                'story',
                'chapter',
            ];
            foreach ($tagtype as $type) {
                $category = $this->createOrGetTag($tag, $type);
                ++$counter;

                $this->categoryRepository->persist($category);
                $this->categoryRepository->flush($counter);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
    }

    private function addUsers(SymfonyStyle $symfonyStyle, PDO $pdo): void
    {
        $stmt        = $pdo->query('SELECT auteur,count(*) as nombre FROM histoires GROUP BY auteur');
        $data        = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $progressBar = new ProgressBar($symfonyStyle, count($data));
        $progressBar->start();

        $counter     = 0;
        foreach ($data as $row) {
            $user   = $this->createOrGetUser($row['auteur']);
            ++$counter;
            $this->userRepository->persist($user);
            $this->userRepository->flush($counter);
            $progressBar->advance();
        }

        $this->userRepository->flush();
        $progressBar->finish();
    }

    private function createOrGetCategory(string $title): ?Category
    {
        if ('' === $title || '0' === $title) {
            return null;
        }

        $existingCategory = $this->categoryRepository->findOneBy(
            [
                'title' => $title,
                'type'  => 'story',
            ]
        );
        if (null !== $existingCategory) {
            return $existingCategory;
        }

        $category = new Category();
        $category->setTitle($title);
        $category->setType('story');

        return $category;
    }

    private function createOrGetTag(string $title, string $type): ?Tag
    {
        if ('' === $title || '0' === $title) {
            return null;
        }

        $existingTag = $this->tagRepository->findOneBy(
            [
                'title' => $title,
                'type'  => $type,
            ]
        );
        if (null !== $existingTag) {
            return $existingTag;
        }

        $tag = new Tag();
        $tag->setTitle($title);
        $tag->setType($type);

        return $tag;
    }

    private function createOrGetUser(string $auteur): User
    {
        // Vérifier si l'utilisateur existe déjà (par nom d'utilisateur ou email)
        $existingUser = $this->userRepository->findUserName($auteur);
        if ($existingUser instanceof User) {
            return $existingUser;
        }

        // Créer un nouvel utilisateur
        $user = new User();
        $user->setUsername($auteur);

        $email = $this->generateUniqueEmail($auteur);
        $user->setEmail($email);
        $password = $this->userService->hashPassword($user, bin2hex(random_bytes(8)));
        $user->setPassword($password);
        $user->setLanguage('fr');
        $user->setEnable(true);

        return $user;
    }

    private function createStory(string $title, array $chapters, User $user): void
    {
        $story = $this->storyRepository->findOneBy(
            [
                'title'   => $title,
                'refuser' => $user,
            ]
        );
        if (!$story instanceof Story) {
            $story = new Story();
            $meta  = new Meta();
            $story->setMeta($meta);
            $story->setTitle($title);
            $story->setEnable(true);
            $story->setRefUser($user);
        }

        if (count($story->getChapters()) === count($chapters)) {
            return;
        }

        // format date 05-09-2024 09:00 sur $chapters[0]['date_publication']
        $dateString = $chapters[array_key_first($chapters)]['date_publication'];
        $date       = DateTime::createFromFormat('d-m-Y H:i', $dateString);
        if ($date instanceof DateTime) {
            $story->setCreatedAt($date);
            $story->setUpdatedAt($date);
        }

        $this->addCategoryTagStory($story, $chapters);
        $this->storyRepository->persist($story);
        $this->storyRepository->flush();

        $chapitre        = 1;
        $numberFormatter = new NumberFormatter('fr_FR', NumberFormatter::DECIMAL);
        foreach ($chapters as $chapter) {
            $this->addChapter($story, $chapter, sprintf('Chapitre %s', $numberFormatter->format($chapitre)));
            ++$chapitre;
        }
    }

    private function deleteAll(): void
    {
        $stories          = $this->storyRepository->findAll();
        $filterCollection = $this->entityManager->getFilters();
        $filterCollection->disable('softdeleteable');
        $filterCollection->enable('deletedfile');
        foreach ($stories as $story) {
            $this->storyRepository->delete($story);
        }
    }

    private function generateUniqueEmail(string $username): string
    {
        $username  = str_replace(['"', "'"], '', $username);
        $baseEmail = strtolower(str_replace(' ', '.', $username)) . '@stories.local';
        $email     = $baseEmail;
        $counter   = 1;

        // Vérifier l'unicité de l'email
        while ($this->userRepository->findOneBy(
            ['email' => $email]
        )) {
            $email = strtolower(str_replace(' ', '.', $username)) . $counter . '@stories.local';
            ++$counter;
        }

        return $email;
    }

    private function getParagraphTextChapter(Chapter $chapter)
    {
        foreach ($chapter->getParagraphs() as $paragraph) {
            if ('text' == $paragraph->getType()) {
                return $paragraph;
            }
        }

        return $this->paragraphService->addParagraph($chapter, 'text');
    }

    /**
     * @return array<string, non-empty-array<(int | non-falsy-string | numeric-string), mixed>>
     */
    private function groupByStory(array $chapters): array
    {
        $stories = [];

        foreach ($chapters as $chapter) {
            $titre = trim((string) $chapter['titre']);
            // Regroupement optimisé des chapitres par histoire et numéro de chapitre
            $patterns = [
                '/^(.*?)(?:\s+|_)?\((\d+)[\/\.-](\d+)\)$/', // Titre (1/10)
                '/^(.*?)(?:\s+|_)?\((\d+)\)$/',              // Titre (1)
                '/^(.*?)(?:\s+|_)?(\d+[\/\.-]\d+)$/',        // Titre 1/10
                '/^(.*?)(?:\s+|_)?(\d+)$/'                   // Titre 1
            ];

            $matched = false;
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $titre, $matches)) {
                    $titre = trim($matches[1]);
                    $idChapter = isset($matches[3]) ? (isset($matches[4]) ? $matches[3] . '/' . $matches[4] : $matches[3]) : $matches[2];
                    $stories[$titre][$idChapter] = $chapter;
                    $matched = true;
                    break;
                }
            }
            if (!$matched) {
                $stories[$titre][] = $chapter;
            }
        }

        return $stories;
    }
}
