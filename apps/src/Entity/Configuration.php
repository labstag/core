<?php

namespace Labstag\Entity;

use DateTime;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Labstag\Entity\Traits\TimestampableTrait;
use Labstag\Repository\ConfigurationRepository;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: ConfigurationRepository::class)]
#[Vich\Uploadable]
class Configuration
{
    use TimestampableTrait;

    #[ORM\Column(name: 'chapter_placeholder', length: 255, nullable: true)]
    private ?string $chapterPlaceholder = null;

    #[Vich\UploadableField(mapping: 'configuration', fileNameProperty: 'chapterPlaceholder')]
    private ?File $chapterPlaceholderFile = null;

    #[ORM\Column(length: 255)]
    private ?string $copyright = null;

    #[ORM\Column(
        type: Types::BOOLEAN,
        options: ['default' => 0]
    )]
    private bool $disableEmptyAgent = false;

    #[ORM\Column(name: 'edito_placeholder', length: 255, nullable: true)]
    private ?string $editoPlaceholder = null;

    #[Vich\UploadableField(mapping: 'configuration', fileNameProperty: 'editoPlaceholder')]
    private ?File $editoPlaceholderFile = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(name: 'episode_placeholder', length: 255, nullable: true)]
    private ?string $episodePlaceholder = null;

    #[Vich\UploadableField(mapping: 'configuration', fileNameProperty: 'episodePlaceholder')]
    private ?File $episodePlaceholderFile = null;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: Types::GUID, unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    #[ORM\Column(name: 'language_tmdb', length: 255, nullable: true)]
    private ?string $languageTmdb = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logo = null;

    #[Vich\UploadableField(mapping: 'configuration', fileNameProperty: 'logo')]
    private ?File $logoFile = null;

    #[ORM\Column(name: 'memo_placeholder', length: 255, nullable: true)]
    private ?string $memoPlaceholder = null;

    #[Vich\UploadableField(mapping: 'configuration', fileNameProperty: 'memoPlaceholder')]
    private ?File $memoPlaceholderFile = null;

    #[ORM\Column(name: 'movie_placeholder', length: 255, nullable: true)]
    private ?string $moviePlaceholder = null;

    #[Vich\UploadableField(mapping: 'configuration', fileNameProperty: 'moviePlaceholder')]
    private ?File $moviePlaceholderFile = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $noreply = null;

    #[ORM\Column(name: 'page_placeholder', length: 255, nullable: true)]
    private ?string $pagePlaceholder = null;

    #[Vich\UploadableField(mapping: 'configuration', fileNameProperty: 'pagePlaceholder')]
    private ?File $pagePlaceholderFile = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $placeholder = null;

    #[Vich\UploadableField(mapping: 'configuration', fileNameProperty: 'placeholder')]
    private ?File $placeholderFile = null;

    #[ORM\Column(name: 'post_placeholder', length: 255, nullable: true)]
    private ?string $postPlaceholder = null;

    #[Vich\UploadableField(mapping: 'configuration', fileNameProperty: 'postPlaceholder')]
    private ?File $postPlaceholderFile = null;

    #[ORM\Column(name: 'saga_placeholder', length: 255, nullable: true)]
    private ?string $sagaPlaceholder = null;

    #[Vich\UploadableField(mapping: 'configuration', fileNameProperty: 'sagaPlaceholder')]
    private ?File $sagaPlaceholderFile = null;

    #[ORM\Column(name: 'season_placeholder', length: 255, nullable: true)]
    private ?string $seasonPlaceholder = null;

    #[Vich\UploadableField(mapping: 'configuration', fileNameProperty: 'seasonPlaceholder')]
    private ?File $seasonPlaceholderFile = null;

    #[ORM\Column(name: 'serie_placeholder', length: 255, nullable: true)]
    private ?string $seriePlaceholder = null;

    #[Vich\UploadableField(mapping: 'configuration', fileNameProperty: 'seriePlaceholder')]
    private ?File $seriePlaceholderFile = null;

    #[ORM\Column(
        type: Types::BOOLEAN,
        options: ['default' => 1]
    )]
    private bool $sitemapPosts = true;

    #[ORM\Column(
        type: Types::BOOLEAN,
        options: ['default' => 1]
    )]
    private bool $sitemapStory = true;

    #[ORM\Column(name: 'star_placeholder', length: 255, nullable: true)]
    private ?string $starPlaceholder = null;

    #[Vich\UploadableField(mapping: 'configuration', fileNameProperty: 'starPlaceholder')]
    private ?File $starPlaceholderFile = null;

    #[ORM\Column(name: 'story_placeholder', length: 255, nullable: true)]
    private ?string $storyPlaceholder = null;

    #[Vich\UploadableField(mapping: 'configuration', fileNameProperty: 'storyPlaceholder')]
    private ?File $storyPlaceholderFile = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tabIconSrc = null;

    #[ORM\Column(
        type: Types::BOOLEAN,
        options: ['default' => 1]
    )]
    private bool $tacAcceptAllCta = true;

    #[ORM\Column(
        type: Types::BOOLEAN,
        options: ['default' => 0]
    )]
    private bool $tacAdblocker = false;

    #[ORM\Column(
        type: Types::BOOLEAN,
        options: ['default' => 0]
    )]
    private bool $tacAlwaysNeedConsent = false;

    #[ORM\Column(
        length: 255,
        nullable: true,
        options: ['default' => 'top']
    )]
    private ?string $tacBodyPosition = 'top';

    #[ORM\Column(
        type: Types::BOOLEAN,
        options: ['default' => 1]
    )]
    private bool $tacClosePopup = true;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tacCookieDomain = null;

    #[ORM\Column(
        length: 255,
        nullable: true,
        options: ['default' => 'rgpd']
    )]
    private ?string $tacCookieName = 'rgpd';

    #[ORM\Column(
        type: Types::BOOLEAN,
        options: ['default' => 0]
    )]
    private bool $tacCookieslist = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tacCustomCloserId = null;

    #[ORM\Column(
        type: Types::BOOLEAN,
        options: ['default' => 1]
    )]
    private bool $tacDenyAllCta = true;

    #[ORM\Column(
        type: Types::BOOLEAN,
        options: ['default' => 1]
    )]
    private bool $tacGoogleConsentMode = true;

    #[ORM\Column(
        type: Types::BOOLEAN,
        options: ['default' => 1]
    )]
    private bool $tacGroupServices = true;

    #[ORM\Column(
        type: Types::BOOLEAN,
        options: ['default' => 0]
    )]
    private bool $tacHandleBrowserDNTRequest = false;

    #[ORM\Column(
        length: 255,
        nullable: true,
        options: ['default' => '#rgpd']
    )]
    private ?string $tacHashtag = '#rgpd';

    #[ORM\Column(
        type: Types::BOOLEAN,
        options: ['default' => 1]
    )]
    private bool $tacHighPrivacy = true;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tacIconPosition = null;

    #[ORM\Column(
        type: Types::BOOLEAN,
        options: ['default' => 1]
    )]
    private bool $tacMandatory = true;

    #[ORM\Column(
        type: Types::BOOLEAN,
        options: ['default' => 0]
    )]
    private bool $tacMandatoryCta = false;

    #[ORM\Column(
        type: Types::BOOLEAN,
        options: ['default' => 1]
    )]
    private bool $tacMoreInfoLink = true;

    #[ORM\Column(
        length: 255,
        nullable: true,
        options: ['default' => 'middle']
    )]
    private ?string $tacOrientation = 'middle';

    #[ORM\Column(
        type: Types::BOOLEAN,
        options: ['default' => 1]
    )]
    private bool $tacPartnersList = true;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tacPrivacyUrl = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tacReadmoreLink = null;

    #[ORM\Column(
        type: Types::BOOLEAN,
        options: ['default' => 0]
    )]
    private bool $tacRemoveCredit = false;

    #[ORM\Column(
        length: 255,
        nullable: true,
        options: ['default' => 'wait']
    )]
    private ?string $tacServiceDefaultState = 'wait';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tacServices = null;

    #[ORM\Column(
        type: Types::BOOLEAN,
        options: ['default' => 1]
    )]
    private bool $tacShowAlertSmall = true;

    #[ORM\Column(
        type: Types::BOOLEAN,
        options: ['default' => 1]
    )]
    private bool $tacShowDetailsOnClick = true;

    #[ORM\Column(
        type: Types::BOOLEAN,
        options: ['default' => 1]
    )]
    private bool $tacShowIcon = true;

    #[ORM\Column(
        type: Types::BOOLEAN,
        options: ['default' => 0]
    )]
    private bool $tacUseExternalCss = false;

    #[ORM\Column(
        type: Types::BOOLEAN,
        options: ['default' => 0]
    )]
    private bool $tacUseExternalJs = false;

    #[ORM\Column(length: 255)]
    private ?string $titleFormat = null;

    #[ORM\Column(length: 255)]
    private ?string $url = null;

    #[ORM\Column(
        type: Types::BOOLEAN,
        options: ['default' => 0]
    )]
    private bool $userLink = false;

    #[ORM\Column(name: 'user_placeholder', length: 255, nullable: true)]
    private ?string $userPlaceholder = null;

    #[Vich\UploadableField(mapping: 'configuration', fileNameProperty: 'userPlaceholder')]
    private ?File $userPlaceholderFile = null;

    #[ORM\Column(
        type: Types::BOOLEAN,
        options: ['default' => 0]
    )]
    private bool $userShow = false;

    public function getChapterPlaceholder(): ?string
    {
        return $this->chapterPlaceholder;
    }

    public function getChapterPlaceholderFile(): ?File
    {
        return $this->chapterPlaceholderFile;
    }

    public function getCopyright(): ?string
    {
        return $this->copyright;
    }

    public function getEditoPlaceholder(): ?string
    {
        return $this->editoPlaceholder;
    }

    public function getEditoPlaceholderFile(): ?File
    {
        return $this->editoPlaceholderFile;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getEpisodePlaceholder(): ?string
    {
        return $this->episodePlaceholder;
    }

    public function getEpisodePlaceholderFile(): ?File
    {
        return $this->episodePlaceholderFile;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getLanguageTmdb(): ?string
    {
        return $this->languageTmdb;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function getLogoFile(): ?File
    {
        return $this->logoFile;
    }

    public function getMemoPlaceholder(): ?string
    {
        return $this->memoPlaceholder;
    }

    public function getMemoPlaceholderFile(): ?File
    {
        return $this->memoPlaceholderFile;
    }

    public function getMoviePlaceholder(): ?string
    {
        return $this->moviePlaceholder;
    }

    public function getMoviePlaceholderFile(): ?File
    {
        return $this->moviePlaceholderFile;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getNoreply(): ?string
    {
        return $this->noreply;
    }

    public function getPagePlaceholder(): ?string
    {
        return $this->pagePlaceholder;
    }

    public function getPagePlaceholderFile(): ?File
    {
        return $this->pagePlaceholderFile;
    }

    public function getPlaceholder(): ?string
    {
        return $this->placeholder;
    }

    public function getPlaceholderFile(): ?File
    {
        return $this->placeholderFile;
    }

    public function getPostPlaceholder(): ?string
    {
        return $this->postPlaceholder;
    }

    public function getPostPlaceholderFile(): ?File
    {
        return $this->postPlaceholderFile;
    }

    public function getSagaPlaceholder(): ?string
    {
        return $this->sagaPlaceholder;
    }

    public function getSagaPlaceholderFile(): ?File
    {
        return $this->sagaPlaceholderFile;
    }

    public function getSeasonPlaceholder(): ?string
    {
        return $this->seasonPlaceholder;
    }

    public function getSeasonPlaceholderFile(): ?File
    {
        return $this->seasonPlaceholderFile;
    }

    public function getSeriePlaceholder(): ?string
    {
        return $this->seriePlaceholder;
    }

    public function getSeriePlaceholderFile(): ?File
    {
        return $this->seriePlaceholderFile;
    }

    public function getStarPlaceholder(): ?string
    {
        return $this->starPlaceholder;
    }

    public function getStarPlaceholderFile(): ?File
    {
        return $this->starPlaceholderFile;
    }

    public function getStoryPlaceholder(): ?string
    {
        return $this->storyPlaceholder;
    }

    public function getStoryPlaceholderFile(): ?File
    {
        return $this->storyPlaceholderFile;
    }

    public function getTabIconSrc(): string
    {
        return (string) $this->tabIconSrc;
    }

    public function getTacBodyPosition(): string
    {
        $bodyPosition = (string) $this->tacBodyPosition;
        if ('' === $bodyPosition) {
            return 'top';
        }

        return $bodyPosition;
    }

    public function getTacCookieDomain(): string
    {
        return (string) $this->tacCookieDomain;
    }

    public function getTacCookieName(): string
    {
        $cookieName = (string) $this->tacCookieName;
        if ('' === $cookieName) {
            return 'rgpd';
        }

        return $cookieName;
    }

    public function getTacCustomCloserId(): string
    {
        return (string) $this->tacCustomCloserId;
    }

    public function getTacGroupServices(): bool
    {
        return $this->tacGroupServices;
    }

    public function getTacHashtag(): string
    {
        $hashtag = (string) $this->tacHashtag;
        if ('' === $hashtag) {
            return '#rgpd';
        }

        return $hashtag;
    }

    public function getTacIconPosition(): string
    {
        $iconPosition = (string) $this->tacIconPosition;
        if ('' === $iconPosition) {
            return 'BottomRight';
        }

        return $iconPosition;
    }

    public function getTacOrientation(): string
    {
        $orientation = (string) $this->tacOrientation;
        if ('' === $orientation) {
            return 'middle';
        }

        return $orientation;
    }

    public function getTacPrivacyUrl(): string
    {
        return (string) $this->tacPrivacyUrl;
    }

    public function getTacReadmoreLink(): string
    {
        return (string) $this->tacReadmoreLink;
    }

    public function getTacServiceDefaultState(): ?string
    {
        $serviceDefaultState = (string) $this->tacServiceDefaultState;
        if ('' === $serviceDefaultState) {
            return 'wait';
        }

        return $serviceDefaultState;
    }

    public function getTacServices(): ?string
    {
        return $this->tacServices;
    }

    public function getTitleFormat(): ?string
    {
        return $this->titleFormat;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function getUserPlaceholder(): ?string
    {
        return $this->userPlaceholder;
    }

    public function getUserPlaceholderFile(): ?File
    {
        return $this->userPlaceholderFile;
    }

    public function isDisableEmptyAgent(): bool
    {
        return $this->disableEmptyAgent;
    }

    public function isSitemapPosts(): bool
    {
        return $this->sitemapPosts;
    }

    public function isSitemapStory(): bool
    {
        return $this->sitemapStory;
    }

    public function isTacAcceptAllCta(): bool
    {
        return $this->tacAcceptAllCta;
    }

    public function isTacAdblocker(): bool
    {
        return $this->tacAdblocker;
    }

    public function isTacAlwaysNeedConsent(): bool
    {
        return $this->tacAlwaysNeedConsent;
    }

    public function isTacClosePopup(): bool
    {
        return $this->tacClosePopup;
    }

    public function isTacCookieslist(): bool
    {
        return $this->tacCookieslist;
    }

    public function isTacDenyAllCta(): bool
    {
        return $this->tacDenyAllCta;
    }

    public function isTacGoogleConsentMode(): bool
    {
        return $this->tacGoogleConsentMode;
    }

    public function isTacHandleBrowserDNTRequest(): bool
    {
        return $this->tacHandleBrowserDNTRequest;
    }

    public function isTacHighPrivacy(): bool
    {
        return $this->tacHighPrivacy;
    }

    public function isTacMandatory(): bool
    {
        return $this->tacMandatory;
    }

    public function isTacMandatoryCta(): bool
    {
        return $this->tacMandatoryCta;
    }

    public function isTacMoreInfoLink(): bool
    {
        return $this->tacMoreInfoLink;
    }

    public function isTacPartnersList(): bool
    {
        return $this->tacPartnersList;
    }

    public function isTacRemoveCredit(): bool
    {
        return $this->tacRemoveCredit;
    }

    public function isTacShowAlertSmall(): bool
    {
        return $this->tacShowAlertSmall;
    }

    public function isTacShowDetailsOnClick(): bool
    {
        return $this->tacShowDetailsOnClick;
    }

    public function isTacShowIcon(): bool
    {
        return $this->tacShowIcon;
    }

    public function isTacUseExternalCss(): bool
    {
        return $this->tacUseExternalCss;
    }

    public function isTacUseExternalJs(): bool
    {
        return $this->tacUseExternalJs;
    }

    public function isUserLink(): bool
    {
        return $this->userLink;
    }

    public function isUserShow(): bool
    {
        return $this->userShow;
    }

    public function setChapterPlaceholder(?string $chapterPlaceholder): void
    {
        $this->chapterPlaceholder = $chapterPlaceholder;
    }

    public function setChapterPlaceholderFile(?File $chapterPlaceholderFile = null): void
    {
        $this->chapterPlaceholderFile = $chapterPlaceholderFile;

        if ($chapterPlaceholderFile instanceof File) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = DateTime::createFromImmutable(new DateTimeImmutable());
        }
    }

    public function setCopyright(string $copyright): static
    {
        $this->copyright = $copyright;

        return $this;
    }

    public function setDisableEmptyAgent(bool $disableEmptyAgent): static
    {
        $this->disableEmptyAgent = $disableEmptyAgent;

        return $this;
    }

    public function setEditoPlaceholder(?string $editoPlaceholder): void
    {
        $this->editoPlaceholder = $editoPlaceholder;
    }

    public function setEditoPlaceholderFile(?File $editoPlaceholderFile = null): void
    {
        $this->editoPlaceholderFile = $editoPlaceholderFile;

        if ($editoPlaceholderFile instanceof File) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = DateTime::createFromImmutable(new DateTimeImmutable());
        }
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function setEpisodePlaceholder(?string $episodePlaceholder): void
    {
        $this->episodePlaceholder = $episodePlaceholder;
    }

    public function setEpisodePlaceholderFile(?File $episodePlaceholderFile = null): void
    {
        $this->episodePlaceholderFile = $episodePlaceholderFile;

        if ($episodePlaceholderFile instanceof File) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = DateTime::createFromImmutable(new DateTimeImmutable());
        }
    }

    public function setLanguageTmdb(?string $languageTmdb): static
    {
        $this->languageTmdb = $languageTmdb;

        return $this;
    }

    public function setLogo(?string $logo): void
    {
        $this->logo = $logo;
    }

    public function setLogoFile(?File $logoFile = null): void
    {
        $this->logoFile = $logoFile;

        if ($logoFile instanceof File) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = DateTime::createFromImmutable(new DateTimeImmutable());
        }
    }

    public function setMemoPlaceholder(?string $memoPlaceholder): void
    {
        $this->memoPlaceholder = $memoPlaceholder;
    }

    public function setMemoPlaceholderFile(?File $memoPlaceholderFile = null): void
    {
        $this->memoPlaceholderFile = $memoPlaceholderFile;

        if ($memoPlaceholderFile instanceof File) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = DateTime::createFromImmutable(new DateTimeImmutable());
        }
    }

    public function setMoviePlaceholder(?string $moviePlaceholder): void
    {
        $this->moviePlaceholder = $moviePlaceholder;
    }

    public function setMoviePlaceholderFile(?File $moviePlaceholderFile = null): void
    {
        $this->moviePlaceholderFile = $moviePlaceholderFile;

        if ($moviePlaceholderFile instanceof File) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = DateTime::createFromImmutable(new DateTimeImmutable());
        }
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function setNoreply(string $noreply): static
    {
        $this->noreply = $noreply;

        return $this;
    }

    public function setPagePlaceholder(?string $pagePlaceholder): void
    {
        $this->pagePlaceholder = $pagePlaceholder;
    }

    public function setPagePlaceholderFile(?File $pagePlaceholderFile = null): void
    {
        $this->pagePlaceholderFile = $pagePlaceholderFile;

        if ($pagePlaceholderFile instanceof File) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = DateTime::createFromImmutable(new DateTimeImmutable());
        }
    }

    public function setPlaceholder(?string $placeholder): void
    {
        $this->placeholder = $placeholder;
    }

    public function setPlaceholderFile(?File $placeholderFile = null): void
    {
        $this->placeholderFile = $placeholderFile;

        if ($placeholderFile instanceof File) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = DateTime::createFromImmutable(new DateTimeImmutable());
        }
    }

    public function setPostPlaceholder(?string $postPlaceholder): void
    {
        $this->postPlaceholder = $postPlaceholder;
    }

    public function setPostPlaceholderFile(?File $postPlaceholderFile = null): void
    {
        $this->postPlaceholderFile = $postPlaceholderFile;

        if ($postPlaceholderFile instanceof File) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = DateTime::createFromImmutable(new DateTimeImmutable());
        }
    }

    public function setSagaPlaceholder(?string $sagaPlaceholder): void
    {
        $this->sagaPlaceholder = $sagaPlaceholder;
    }

    public function setSagaPlaceholderFile(?File $sagaPlaceholderFile = null): void
    {
        $this->sagaPlaceholderFile = $sagaPlaceholderFile;

        if ($sagaPlaceholderFile instanceof File) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = DateTime::createFromImmutable(new DateTimeImmutable());
        }
    }

    public function setSeasonPlaceholder(?string $seasonPlaceholder): void
    {
        $this->seasonPlaceholder = $seasonPlaceholder;
    }

    public function setSeasonPlaceholderFile(?File $seasonPlaceholderFile = null): void
    {
        $this->seasonPlaceholderFile = $seasonPlaceholderFile;

        if ($seasonPlaceholderFile instanceof File) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = DateTime::createFromImmutable(new DateTimeImmutable());
        }
    }

    public function setSeriePlaceholder(?string $seriePlaceholder): void
    {
        $this->seriePlaceholder = $seriePlaceholder;
    }

    public function setSeriePlaceholderFile(?File $seriePlaceholderFile = null): void
    {
        $this->seriePlaceholderFile = $seriePlaceholderFile;

        if ($seriePlaceholderFile instanceof File) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = DateTime::createFromImmutable(new DateTimeImmutable());
        }
    }

    public function setSitemapPosts(bool $sitemapPosts): static
    {
        $this->sitemapPosts = $sitemapPosts;

        return $this;
    }

    public function setSitemapStory(bool $sitemapStory): static
    {
        $this->sitemapStory = $sitemapStory;

        return $this;
    }

    public function setStarPlaceholder(?string $starPlaceholder): void
    {
        $this->starPlaceholder = $starPlaceholder;
    }

    public function setStarPlaceholderFile(?File $starPlaceholderFile = null): void
    {
        $this->starPlaceholderFile = $starPlaceholderFile;

        if ($starPlaceholderFile instanceof File) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = DateTime::createFromImmutable(new DateTimeImmutable());
        }
    }

    public function setStoryPlaceholder(?string $storyPlaceholder): void
    {
        $this->storyPlaceholder = $storyPlaceholder;
    }

    public function setStoryPlaceholderFile(?File $storyPlaceholderFile = null): void
    {
        $this->storyPlaceholderFile = $storyPlaceholderFile;

        if ($storyPlaceholderFile instanceof File) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = DateTime::createFromImmutable(new DateTimeImmutable());
        }
    }

    public function setTabIconSrc(?string $tabIconSrc): static
    {
        $this->tabIconSrc = $tabIconSrc;

        return $this;
    }

    public function setTacAcceptAllCta(bool $tacAcceptAllCta): static
    {
        $this->tacAcceptAllCta = $tacAcceptAllCta;

        return $this;
    }

    public function setTacAdblocker(bool $tacAdblocker): static
    {
        $this->tacAdblocker = $tacAdblocker;

        return $this;
    }

    public function setTacAlwaysNeedConsent(bool $tacAlwaysNeedConsent): static
    {
        $this->tacAlwaysNeedConsent = $tacAlwaysNeedConsent;

        return $this;
    }

    public function setTacBodyPosition(?string $tacBodyPosition): static
    {
        $this->tacBodyPosition = $tacBodyPosition;

        return $this;
    }

    public function setTacClosePopup(bool $tacClosePopup): static
    {
        $this->tacClosePopup = $tacClosePopup;

        return $this;
    }

    public function setTacCookieDomain(?string $tacCookieDomain): static
    {
        $this->tacCookieDomain = $tacCookieDomain;

        return $this;
    }

    public function setTacCookieName(?string $tacCookieName): static
    {
        $this->tacCookieName = $tacCookieName;

        return $this;
    }

    public function setTacCookieslist(bool $tacCookieslist): static
    {
        $this->tacCookieslist = $tacCookieslist;

        return $this;
    }

    public function setTacCustomCloserId(?string $tacCustomCloserId): static
    {
        $this->tacCustomCloserId = $tacCustomCloserId;

        return $this;
    }

    public function setTacDenyAllCta(bool $tacDenyAllCta): static
    {
        $this->tacDenyAllCta = $tacDenyAllCta;

        return $this;
    }

    public function setTacGoogleConsentMode(bool $tacGoogleConsentMode): static
    {
        $this->tacGoogleConsentMode = $tacGoogleConsentMode;

        return $this;
    }

    public function setTacGroupServices(bool $tacGroupServices): static
    {
        $this->tacGroupServices = $tacGroupServices;

        return $this;
    }

    public function setTacHandleBrowserDNTRequest(bool $tacHandleBrowserDNTRequest): static
    {
        $this->tacHandleBrowserDNTRequest = $tacHandleBrowserDNTRequest;

        return $this;
    }

    public function setTacHashtag(?string $tacHashtag): static
    {
        $this->tacHashtag = $tacHashtag;

        return $this;
    }

    public function setTacHighPrivacy(bool $tacHighPrivacy): static
    {
        $this->tacHighPrivacy = $tacHighPrivacy;

        return $this;
    }

    public function setTacIconPosition(?string $tacIconPosition): static
    {
        $this->tacIconPosition = $tacIconPosition;

        return $this;
    }

    public function setTacMandatory(bool $tacMandatory): static
    {
        $this->tacMandatory = $tacMandatory;

        return $this;
    }

    public function setTacMandatoryCta(bool $tacMandatoryCta): static
    {
        $this->tacMandatoryCta = $tacMandatoryCta;

        return $this;
    }

    public function setTacMoreInfoLink(bool $tacMoreInfoLink): static
    {
        $this->tacMoreInfoLink = $tacMoreInfoLink;

        return $this;
    }

    public function setTacOrientation(?string $tacOrientation): static
    {
        $this->tacOrientation = $tacOrientation;

        return $this;
    }

    public function setTacPartnersList(bool $tacPartnersList): static
    {
        $this->tacPartnersList = $tacPartnersList;

        return $this;
    }

    public function setTacPrivacyUrl(?string $tacPrivacyUrl): static
    {
        $this->tacPrivacyUrl = $tacPrivacyUrl;

        return $this;
    }

    public function setTacReadmoreLink(?string $tacReadmoreLink): static
    {
        $this->tacReadmoreLink = $tacReadmoreLink;

        return $this;
    }

    public function setTacRemoveCredit(bool $tacRemoveCredit): static
    {
        $this->tacRemoveCredit = $tacRemoveCredit;

        return $this;
    }

    public function setTacServiceDefaultState(?string $tacServiceDefaultState): static
    {
        $this->tacServiceDefaultState = $tacServiceDefaultState;

        return $this;
    }

    public function setTacServices(?string $tacServices): static
    {
        $this->tacServices = $tacServices;

        return $this;
    }

    public function setTacShowAlertSmall(bool $tacShowAlertSmall): static
    {
        $this->tacShowAlertSmall = $tacShowAlertSmall;

        return $this;
    }

    public function setTacShowDetailsOnClick(bool $tacShowDetailsOnClick): static
    {
        $this->tacShowDetailsOnClick = $tacShowDetailsOnClick;

        return $this;
    }

    public function setTacShowIcon(bool $tacShowIcon): static
    {
        $this->tacShowIcon = $tacShowIcon;

        return $this;
    }

    public function setTacUseExternalCss(bool $tacUseExternalCss): static
    {
        $this->tacUseExternalCss = $tacUseExternalCss;

        return $this;
    }

    public function setTacUseExternalJs(bool $tacUseExternalJs): static
    {
        $this->tacUseExternalJs = $tacUseExternalJs;

        return $this;
    }

    public function setTitleFormat(string $titleFormat): static
    {
        $this->titleFormat = $titleFormat;

        return $this;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function setUserLink(bool $userLink): static
    {
        $this->userLink = $userLink;

        return $this;
    }

    public function setUserPlaceholder(?string $userPlaceholder): void
    {
        $this->userPlaceholder = $userPlaceholder;
    }

    public function setUserPlaceholderFile(?File $userPlaceholderFile = null): void
    {
        $this->userPlaceholderFile = $userPlaceholderFile;

        if ($userPlaceholderFile instanceof File) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = DateTime::createFromImmutable(new DateTimeImmutable());
        }
    }

    public function setUserShow(bool $userShow): static
    {
        $this->userShow = $userShow;

        return $this;
    }
}
