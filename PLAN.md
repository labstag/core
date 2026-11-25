# Plan d'amélioration — Labstag

Priorités proposées (à valider).

## 1. Expérience développeur
- Permettre l'exécution des tests unitaires sans la stack complète (SQLite déjà utilisé).

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

## 7. Gouvernance Git
- Protéger les branches `main`/`develop` sur GitHub.