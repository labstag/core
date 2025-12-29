<?php

namespace Labstag\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Labstag\Entity\Traits\TimestampableTrait;
use Labstag\Repository\ParagraphRepository;
use Override;
use Stringable;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

#[ORM\Table(name: 'paragraph')]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\Entity(repositoryClass: ParagraphRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap(
    [
        'chapter-lastnext' => ChapterLastNextParagraph::class,
        'chapter-list'     => ChapterListParagraph::class,
        'edito'            => EditoParagraph::class,
        'episode-list'     => EpisodeListParagraph::class,
        'error'            => ErrorParagraph::class,
        'experiences'      => ExperiencesParagraph::class,
        'form'             => FormParagraph::class,
        'game'             => GameParagraph::class,
        'head-chapter'     => HeadChapterParagraph::class,
        'head-cv'          => HeadCvParagraph::class,
        'head-game'        => HeadGameParagraph::class,
        'head-movie'       => HeadMovieParagraph::class,
        'head-post'        => HeadPostParagraph::class,
        'head-saga'        => HeadSagaParagraph::class,
        'head-season'      => HeadSeasonParagraph::class,
        'head-serie'       => HeadSerieParagraph::class,
        'head-story'       => HeadStoryParagraph::class,
        'head'             => HeadParagraph::class,
        'hero'             => HeroParagraph::class,
        'html'             => HtmlParagraph::class,
        'img'              => ImageParagraph::class,
        'last-news'        => LastNewsParagraph::class,
        'last-story'       => LastStoryParagraph::class,
        'map'              => MapParagraph::class,
        'movie-info'       => MovieInfoParagraph::class,
        'movie-slider'     => MovieSliderParagraph::class,
        'movie'            => MovieParagraph::class,
        'news-list'        => NewsListParagraph::class,
        'saga-list'        => SagaListParagraph::class,
        'saga'             => SagaParagraph::class,
        'season-list'      => SeasonListParagraph::class,
        'serie'            => SerieParagraph::class,
        'sibling'          => SiblingParagraph::class,
        'sitemap'          => SitemapParagraph::class,
        'skills'           => SkillsParagraph::class,
        'star'             => StarParagraph::class,
        'story-list'       => StoryListParagraph::class,
        'text-img'         => TextImgParagraph::class,
        'text-media'       => TextMediaParagraph::class,
        'text'             => TextParagraph::class,
        'trainingcourses'  => TrainingCoursesParagraph::class,
        'video'            => VideoParagraph::class,
    ]
)]
abstract class Paragraph implements Stringable
{
    use SoftDeleteableEntity;
    use TimestampableTrait;

    #[ORM\ManyToOne(cascade: ['persist', 'detach'], inversedBy: 'paragraphs')]
    protected ?Block $block = null;

    #[ORM\ManyToOne(cascade: ['persist', 'detach'], inversedBy: 'paragraphs')]
    protected ?Chapter $chapter = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $classes = null;

    #[ORM\ManyToOne(cascade: ['persist', 'detach'], inversedBy: 'paragraphs')]
    protected ?Edito $edito = null;

    #[ORM\ManyToOne(cascade: ['persist', 'detach'], inversedBy: 'paragraphs')]
    protected ?Person $person = null;

    #[ORM\Column(
        type: Types::BOOLEAN,
        options: ['default' => 1]
    )]
    protected bool $enable = true;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $fond = null;

    #[ORM\ManyToOne(cascade: ['persist', 'detach'], inversedBy: 'paragraphs')]
    protected ?Game $game = null;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: Types::GUID, unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    protected ?string $id = null;

    #[ORM\ManyToOne(cascade: ['persist', 'detach'], inversedBy: 'paragraphs')]
    protected ?Memo $memo = null;

    #[ORM\ManyToOne(cascade: ['persist', 'detach'], inversedBy: 'paragraphs')]
    protected ?Movie $movie = null;

    #[ORM\ManyToOne(cascade: ['persist', 'detach'], inversedBy: 'paragraphs')]
    protected ?Page $page = null;

    #[ORM\Column]
    protected ?int $position = null;

    #[ORM\ManyToOne(cascade: ['persist', 'detach'], inversedBy: 'paragraphs')]
    protected ?Post $post = null;

    #[ORM\ManyToOne(cascade: ['persist', 'detach'], inversedBy: 'paragraphs')]
    protected ?Saga $saga = null;

    #[ORM\ManyToOne(cascade: ['persist', 'detach'], inversedBy: 'paragraphs')]
    protected ?Season $season = null;

    #[ORM\ManyToOne(cascade: ['persist', 'detach'], inversedBy: 'paragraphs')]
    protected ?Serie $serie = null;

    #[ORM\ManyToOne(cascade: ['persist', 'detach'], inversedBy: 'paragraphs')]
    protected ?Story $story = null;

    #[Override]
    public function __toString(): string
    {
        return (string) $this->getId();
    }

    public function getBlock(): ?Block
    {
        return $this->block;
    }

    public function getChapter(): ?Chapter
    {
        return $this->chapter;
    }

    public function getClasses(): ?string
    {
        return $this->classes;
    }

    public function getEdito(): ?Edito
    {
        return $this->edito;
    }

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function getFond(): ?string
    {
        return $this->fond;
    }

    public function getGame(): ?Game
    {
        return $this->game;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getMemo(): ?Memo
    {
        return $this->memo;
    }

    public function getMovie(): ?Page
    {
        return $this->movie;
    }

    public function getPage(): ?Page
    {
        return $this->page;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function getSaga(): ?Serie
    {
        return $this->saga;
    }

    public function getSeason(): ?Season
    {
        return $this->season;
    }

    public function getSerie(): ?Serie
    {
        return $this->serie;
    }

    public function getStory(): ?Story
    {
        return $this->story;
    }

    public function isEnable(): ?bool
    {
        return $this->enable;
    }

    public function setBlock(?Block $block): static
    {
        $this->block = $block;

        return $this;
    }

    public function setChapter(?Chapter $chapter): static
    {
        $this->chapter = $chapter;

        return $this;
    }

    public function setClasses(?string $classes): static
    {
        $this->classes = $classes;

        return $this;
    }

    public function setEdito(?Edito $edito): static
    {
        $this->edito = $edito;

        return $this;
    }

    public function setPerson(?Person $person): static
    {
        $this->person = $person;

        return $this;
    }

    public function setEnable(bool $enable): static
    {
        $this->enable = $enable;

        return $this;
    }

    public function setFond(?string $fond): static
    {
        $this->fond = $fond;

        return $this;
    }

    public function setGame(?Game $game): static
    {
        $this->game = $game;

        return $this;
    }

    public function setMemo(?Memo $memo): static
    {
        $this->memo = $memo;

        return $this;
    }

    public function setMovie(?Movie $movie): static
    {
        $this->movie = $movie;

        return $this;
    }

    public function setPage(?Page $page): static
    {
        $this->page = $page;

        return $this;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function setPost(?Post $post): static
    {
        $this->post = $post;

        return $this;
    }

    public function setSaga(?Saga $saga): static
    {
        $this->saga = $saga;

        return $this;
    }

    public function setSeason(?Season $season): static
    {
        $this->season = $season;

        return $this;
    }

    public function setSerie(?Serie $serie): static
    {
        $this->serie = $serie;

        return $this;
    }

    public function setStory(?Story $story): static
    {
        $this->story = $story;

        return $this;
    }
}
