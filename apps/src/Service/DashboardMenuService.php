<?php

declare(strict_types=1);

namespace Labstag\Service;

use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use Labstag\Controller\Admin\BanIpCrudController;
use Labstag\Controller\Admin\BlockCrudController;
use Labstag\Controller\Admin\ChapterCrudController;
use Labstag\Controller\Admin\CompanyCrudController;
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
        $translatableStoryMessage     = new TranslatableMessage('Story');
        $translatableChapterMessage   = new TranslatableMessage('Chapter');
        $translatableMovieMessage     = new TranslatableMessage('Movie');
        $translatableSagasMessage     = new TranslatableMessage('Sagas');
        $translatableSerieMessage     = new TranslatableMessage('Serie');
        $translatableSeasonMessage    = new TranslatableMessage('Season');
        $translatableEpisodeMessage   = new TranslatableMessage('Episode');
        $translatableGameMessage      = new TranslatableMessage('Game');
        $translatablePlatformMessage  = new TranslatableMessage('Platform');
        $translatableFranchiseMessage = new TranslatableMessage('Franchise');
        $translatablePageMessage      = new TranslatableMessage('Page');
        $translatablePostMessage      = new TranslatableMessage('Post');
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
                new TranslatableMessage('Person'),
                'fas fa-users',
                PersonCrudController::class,
            ],
            [
                new TranslatableMessage('Company'),
                'fas fa-building',
                CompanyCrudController::class,
            ],
            [
                new TranslatableMessage('Edito'),
                'fas fa-info',
                EditoCrudController::class,
            ],
            [
                new TranslatableMessage('Memo'),
                'fas fa-memory',
                MemoCrudController::class,
            ],
            [
                new TranslatableMessage('Media'),
                'fas fa-photo-video',
                MediaCrudController::class,
            ],
            [
                new TranslatableMessage('Meta'),
                'fa fa-file-alt',
                MetaCrudController::class,
            ],
            [
                new TranslatableMessage('Paragraph'),
                'fa fa-paragraph',
                ParagraphCrudController::class,
            ],
            [
                new TranslatableMessage('Block'),
                'fa fa-cubes',
                BlockCrudController::class,
            ],
            [
                new TranslatableMessage('Geocode'),
                'fas fa-map-signs',
                GeoCodeCrudController::class,
            ],
            [
                new TranslatableMessage('Star'),
                'fas fa-star',
                StarCrudController::class,
            ],
            [
                new TranslatableMessage('User'),
                'fa fa-user',
                UserCrudController::class,
            ],
            [
                new TranslatableMessage('Group'),
                'fa fa-users',
                GroupCrudController::class,
            ],
            [
                new TranslatableMessage('Ban IP'),
                'fas fa-ban',
                BanIpCrudController::class,
            ],
            [
                new TranslatableMessage('Redirection'),
                'fas fa-directions',
                RedirectionCrudController::class,
            ],
            [
                new TranslatableMessage('Http error Logs'),
                'fas fa-clipboard-list',
                HttpErrorLogsCrudController::class,
            ],
            [
                new TranslatableMessage('Submission'),
                'fas fa-clipboard-list',
                SubmissionCrudController::class,
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
