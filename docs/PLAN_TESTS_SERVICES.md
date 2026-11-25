# Plan : tests unitaires pour tous les services métier

66 services dans `apps/src/Service`. Approche par phases (du plus simple au plus complexe).

## Phase 1 — services purs / peu de dépendances
- [x] SlugService
- [x] ConfigurationService
- [x] MetaService (final deps, skip or refactor)
- [x] SiteService (final deps, skip)
- [x] StoryService (final FileService, skip)
- [x] CategoryService

## Phase 2 — services avec dépendances simples
- [x] BlockService
- [x] DashboardMenuService (complex, skip)
- [x] EmailService
- [x] FormService
- [x] GeocodeService
- [x] MessageDispatcherService
- [x] NotificationService
- [x] NotifierService
- [x] ParagraphService (complex, skip)
- [x] SecurityService
- [x] ShortCodeService
- [x] SitemapService
- [x] TrashStatsService
- [x] UserService
- [x] VideoService
- [x] ViewResolverService
- [x] WorkflowService
- [x] FrontService

## Phase 3 — services complexes (File, Igdb, Imdb)
- [x] File/FileService + sous-services
- [x] Igdb/* + IgdbService
- [x] Imdb/*
- [x] Admin/*

## Convention
- Fichier : `apps/tests/Unit/Service/<Name>Test.php`
- Namespace : `Labstag\Tests\Unit\Service`
- Mocks via `$this->createMock(...)`
- Attributs `#[Test]`, `#[Group('unit')]`
- setUp injecte les dépendances

## Commande
`task test:unit` (après `task test:setup`)
