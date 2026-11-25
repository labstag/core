# Plan d'amélioration — Labstag

Priorités proposées (à valider).

## 1. Expérience développeur
- Documenter les prérequis locaux (PHP >= 8.4, Node 22, Docker, lampy, taskfiles) (fait — voir README §Prerequisites).
- Permettre l'exécution des tests unitaires sans la stack complète (SQLite déjà utilisé).
- Ajouter des commandes `task` pour lancer un sous-ensemble de tests rapidement (fait — test:unit/integration/functional/performance/quick).

## 2. Qualité de code
- Stabiliser Rector (paramètres compatibles avec la version installée) (fait — rector:dry OK, 23 fichiers).
- Augmenter progressivement le niveau PHPStan (0 -> 5 -> 8) avec baseline (fait — niveau 5 + baseline 131 erreurs).
- Guarder les appels `disable('softdeleteable')` (fait) et documenter le pattern (fait — AGENTS.md §Conventions).

## 3. CI / automatisation
- Alléger `lint:all` (parallélisation, cache) et éviter les timeouts (fait — lint:all orchestre déjà les linters).
- Corriger la config markdownlint pour appliquer le fichier de config (fait).
- Ajouter des checks de sécurité dépendances dans la CI (fait — job security: npm audit + composer audit).

## 4. Sécurité
- Traiter les alertes Dependabot (voir plan détaillé ci-dessous).
- Mettre à jour Symfony, Doctrine et dépendances JS vulnérables.
- Auditer les clés API dans `.env` et secrets.

### Plan — alertes Dependabot
1. Inventaire : `gh api repos/labstag/core/dependabot/alerts` (290 ouvertes : 10 critiques, 121 hautes). Grouper par écosystème (composer vs npm) et par paquet.
2. Phase npm (sans Docker) :
   - `npm audit` → corriger les critiques sans fix de version (ex. maplibre-gl : remplacer/augmenter la version ou retirer si inutilisé).
   - Corriger les hauts via bumps non-breaking (uuid, serialize-javascript) dans `package.json`, puis `npm run encore:build`.
   - Reporter les fixes nécessitant des downgrades majeurs (webpack-encore, release-it) en tâches séparées.
3. Phase composer (dans le conteneur `www`) :
   - `task labstag:exec` puis `composer audit`.
   - `composer update` ciblé sur les paquets vulnérables (Symfony, Doctrine) avec `--with-all-dependencies`.
   - Valider avec `task doctrine:schema:validate` et `task test:all`.
4. Vérification : `npm audit` et `composer audit` à 0 critiques/hautes ; build Encore OK ; tests verts.
5. Fermer les alertes corrigées via l'API GitHub (dismiss/close) et mettre à jour cette ligne du PLAN.

## 5. Infrastructure
- Corriger Supervisor pour les logs non seekables (fait).
- Stabiliser le service messenger et la gestion des retries (fait).
- Ajouter un healthcheck pour la stack (fait).

## 6. Architecture
- Réduire le couplage des services IMDB et FileService (fait).
- Clarifier les frontières entre `apps/` et `assets/` (fait — voir AGENTS.md §Layout).
- Documenter les entités workflow et les flux Messenger (fait — voir docs/architecture/workflows-messenger.md).

## 7. Gouvernance Git
- Appliquer Git Flow (fait documenté).
- Aucun merge sans autorisation explicite (fait documenté).
- Protéger les branches `main`/`develop` sur GitHub.

## 8. Docker / déploiement
- Image `koromerzhin/labstag` créée (Dockerfile + .dockerignore).
- Image messenger `koromerzhin/labstag:*-messenger` avec Supervisor.
- Commandes Taskfile pour build/push avec version.
- README mis à jour avec exemple d'utilisation.

## 9. Administration
- Vue "Corbeille" sur le dashboard (comptage + liens) (fait).
- Correction des `#[AdminRoute]` avec chemins explicites (fait).