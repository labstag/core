<?php

declare(strict_types=1);

namespace Labstag\Service;

use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use Labstag\Controller\Admin\BanIpCrudController;
use Labstag\Controller\Admin\BlockCrudController;
use Labstag\Controller\Admin\ChapterCrudController;
use Labstag\Controller\Admin\CompanyCrudController;
use Labstag\Controller\Admin\ConfigurationCrudController;
use Labstag\Controller\Admin\EditoCrudController;
use Labstag\Controller\Admin\EpisodeCrudController;
use Labstag\Controller\Admin\Factory\MenuItemFactory;
use Labstag\Controller\Admin\FranchiseCrudController;
use Labstag\Controller\Admin\GameCrudController;
use Labstag\Controller\Admin\GeoCodeCrudController;
use Labstag\Controller\Admin\GroupCrudController;
use Labstag\Controller\Admin\HttpErrorLogsCrudController;
use Labstag\Controller\Admin\MediaCrudController;
use Labstag\Controller\Admin\MemoCrudController;
use Labstag\Controller\Admin\MetaCrudController;
use Labstag\Controller\Admin\MovieCrudController;
use Labstag\Controller\Admin\PageCrudController;
use Labstag\Controller\Admin\ParagraphCrudController;
use Labstag\Controller\Admin\PersonCrudController;
use Labstag\Controller\Admin\PlatformCrudController;
use Labstag\Controller\Admin\PostCrudController;
use Labstag\Controller\Admin\RedirectionCrudController;
use Labstag\Controller\Admin\SagaCrudController;
use Labstag\Controller\Admin\SeasonCrudController;
use Labstag\Controller\Admin\SerieCrudController;
use Labstag\Controller\Admin\StarCrudController;
use Labstag\Controller\Admin\StoryCrudController;
use Labstag\Controller\Admin\SubmissionCrudController;
use Labstag\Controller\Admin\UserCrudController;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatableMessage;

final class DashboardMenuService
{
    public function __construct(
        private MenuItemFactory $menuItemFactory,
        private UrlGeneratorInterface $urlGenerator,
    )
    {
    }

    public function buildContentMenus(array $categories, array $tags): iterable
    {
        // Definition: identifier, label, icon, controller, categories?, tags?, extra children
        $translatableStoryMessage     = new TranslatableMessage(StoryCrudController::getEntityLabel());
        $translatableChapterMessage   = new TranslatableMessage(ChapterCrudController::getEntityLabel());
        $translatableMovieMessage     = new TranslatableMessage(MovieCrudController::getEntityLabel());
        $translatableSagasMessage     = new TranslatableMessage(SagaCrudController::getEntityLabel());
        $translatableSerieMessage     = new TranslatableMessage(SerieCrudController::getEntityLabel());
        $translatableSeasonMessage    = new TranslatableMessage(SeasonCrudController::getEntityLabel());
        $translatableEpisodeMessage   = new TranslatableMessage(EpisodeCrudController::getEntityLabel());
        $translatableGameMessage      = new TranslatableMessage(GameCrudController::getEntityLabel());
        $translatablePlatformMessage  = new TranslatableMessage(PlatformCrudController::getEntityLabel());
        $translatableFranchiseMessage = new TranslatableMessage(FranchiseCrudController::getEntityLabel());
        $translatablePageMessage      = new TranslatableMessage(PageCrudController::getEntityLabel());
        $translatablePostMessage      = new TranslatableMessage(PostCrudController::getEntityLabel());
        $definitions                  = [
            [
                'story',
                $translatableStoryMessage->getMessage(),
                'fas fa-landmark',
                StoryCrudController::class,
                $categories,
                $tags,
                [],
                false,
            ],
            [
                'chapter',
                $translatableChapterMessage->getMessage(),
                'fas fa-landmark',
                ChapterCrudController::class,
                null,
                null,
                [],
                false,
            ],
            [
                'movie',
                $translatableMovieMessage->getMessage(),
                'fas fa-film',
                MovieCrudController::class,
                $categories,
                null,
                [
                    MenuItem::linkTo(
                        SagaCrudController::class,
                        $translatableSagasMessage->getMessage(),
                        'fas fa-video'
                    ),
                ],
                true,
            ],
            [
                'serie',
                $translatableSerieMessage->getMessage(),
                'fas fa-film',
                SerieCrudController::class,
                $categories,
                null,
                [
                    MenuItem::linkTo(
                        SeasonCrudController::class,
                        $translatableSeasonMessage->getMessage(),
                        'fas fa-video'
                    ),
                    MenuItem::linkTo(
                        EpisodeCrudController::class,
                        $translatableEpisodeMessage->getMessage(),
                        'fas fa-video'
                    ),
                ],
                true,
            ],
            [
                'game',
                $translatableGameMessage->getMessage(),
                'fas fa-gamepad',
                GameCrudController::class,
                $categories,
                null,
                [
                    MenuItem::linkTo(
                        PlatformCrudController::class,
                        $translatablePlatformMessage->getMessage(),
                        'fas fa-desktop'
                    ),
                    MenuItem::linkTo(
                        FranchiseCrudController::class,
                        $translatableFranchiseMessage->getMessage(),
                        'fas fa-th-large'
                    ),
                ],
                true,
            ],
            [
                'page',
                $translatablePageMessage->getMessage(),
                'fas fa-columns',
                PageCrudController::class,
                $categories,
                $tags,
                [],
                false,
            ],
            [
                'post',
                $translatablePostMessage->getMessage(),
                'fas fa-newspaper',
                PostCrudController::class,
                $categories,
                $tags,
                [],
                false,
            ],
        ];

        foreach ($definitions as [$code, $label, $icon, $controller, $cats, $tgs, $children, $disableAdd]) {
            yield $this->menuItemFactory->createContentSubMenu(
                $code,
                $label,
                $icon,
                $controller,
                $disableAdd,
                $cats,
                $tgs,
                $children
            );
        }
    }

    public function buildSimpleCrudMenus(): iterable
    {
        $items = [
            [
                new TranslatableMessage(PersonCrudController::getEntityLabel()),
                'fas fa-users',
                PersonCrudController::class,
            ],
            [
                new TranslatableMessage(CompanyCrudController::getEntityLabel()),
                'fas fa-building',
                CompanyCrudController::class,
            ],
            [
                new TranslatableMessage(EditoCrudController::getEntityLabel()),
                'fas fa-info',
                EditoCrudController::class,
            ],
            [
                new TranslatableMessage(MemoCrudController::getEntityLabel()),
                'fas fa-memory',
                MemoCrudController::class,
            ],
            [
                new TranslatableMessage(MediaCrudController::getEntityLabel()),
                'fas fa-photo-video',
                MediaCrudController::class,
            ],
            [
                new TranslatableMessage(MetaCrudController::getEntityLabel()),
                'fa fa-file-alt',
                MetaCrudController::class,
            ],
            [
                new TranslatableMessage(ParagraphCrudController::getEntityLabel()),
                'fa fa-paragraph',
                ParagraphCrudController::class,
            ],
            [
                new TranslatableMessage(BlockCrudController::getEntityLabel()),
                'fa fa-cubes',
                BlockCrudController::class,
            ],
            [
                new TranslatableMessage(GeoCodeCrudController::getEntityLabel()),
                'fas fa-map-signs',
                GeoCodeCrudController::class,
            ],
            [
                new TranslatableMessage(StarCrudController::getEntityLabel()),
                'fas fa-star',
                StarCrudController::class,
            ],
            [
                new TranslatableMessage(UserCrudController::getEntityLabel()),
                'fa fa-user',
                UserCrudController::class,
            ],
            [
                new TranslatableMessage(GroupCrudController::getEntityLabel()),
                'fa fa-users',
                GroupCrudController::class,
            ],
            [
                new TranslatableMessage(BanIpCrudController::getEntityLabel()),
                'fas fa-ban',
                BanIpCrudController::class,
            ],
            [
                new TranslatableMessage(RedirectionCrudController::getEntityLabel()),
                'fas fa-directions',
                RedirectionCrudController::class,
            ],
            [
                new TranslatableMessage(HttpErrorLogsCrudController::getEntityLabel()),
                'fas fa-clipboard-list',
                HttpErrorLogsCrudController::class,
            ],
            [
                new TranslatableMessage(SubmissionCrudController::getEntityLabel()),
                'fas fa-clipboard-list',
                SubmissionCrudController::class,
            ],
            [
                new TranslatableMessage(ConfigurationCrudController::getEntityLabel()),
                'fas fa-clipboard-list',
                ConfigurationCrudController::class,
            ],
        ];

        foreach ($items as [$label, $icon, $fqcn]) {
            yield MenuItem::linkTo($fqcn, $label->getMessage(), $icon);
        }

        yield MenuItem::linkToRoute(
            new TranslatableMessage('Permissions'),
            'fa fa-user-shield',
            'admin_permission'
        );
    }

    public function buildUtilityMenus(): iterable
    {
        yield MenuItem::linkToUrl(
            new TranslatableMessage('Clear Cache'),
            'fas fa-trash',
            $this->urlGenerator->generate('admin_cacheclear')
        );

        yield MenuItem::linkToUrl(
            new TranslatableMessage('View Site'),
            'fas fa-laptop-house',
            $this->urlGenerator->generate('front')
        )->setLinkTarget('_blank');
    }

}
