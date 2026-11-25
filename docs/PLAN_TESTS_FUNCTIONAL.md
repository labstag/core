# Plan : tests fonctionnels

Smoke tests HTTP sur les routes principales, via `AbstractWebTestCase`.
Convention : `apps/tests/Functional/Controller/<Name>Test.php`, namespace `Labstag\Tests\Functional\Controller`, attributs `#[Test]`, `#[Group('functional')]`.

## Phase 1 — routes publiques (FrontController)
- [x] `/` homepage (PublicRoutesTest)
- [x] `/sitemap.xml`, `/sitemap.css`, `/sitemap.js`, `/sitemap.xsl`
- [x] Login page (`/login`, `/connexion`) — AdditionalRoutesTest
- [ ] Pages statiques via `/{slug}/{page}` — nécessite fixtures/workflow (reporté)
- [ ] Catégories / tags / archives — via `/{slug}/{page}` (reporté)
- [ ] Recherche — aucune route dédiée trouvée (reporté)

## Phase 2 — authentification (SecurityController)
- [x] `/connexion` GET
- [x] `/login` GET
- [x] `/logout`
- [x] login POST invalid
- [ ] login POST valide (nécessite user fixture + session)
- [ ] register (si activé)
- [ ] reset password

## Phase 3 — admin (AdminController + CRUD)
- [x] `/admin` dashboard (AdminControllerTest)
- [x] Tous les CrudController index (AdminIndexTest, 85 tests)
- [ ] new/edit/delete smoke (optionnel, reporté)

## Phase 4 — routes additionnelles
- [x] `AdditionalRoutesTest` existant étendu
- [x] `/login` + `/sitemap.xml` smoke tests

## Commande
`task test:functional` (après `task test:setup`)
