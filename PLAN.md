# Plan d'amélioration — Labstag

Priorités proposées (à valider).

## 1. Expérience développeur
- Documenter les prérequis locaux (PHP >= 8.4, Node 22, Docker, lampy, taskfiles).
- Permettre l'exécution des tests unitaires sans la stack complète (SQLite déjà utilisé).
- Ajouter des commandes `task` pour lancer un sous-ensemble de tests rapidement.

## 2. Qualité de code
- Stabiliser Rector (paramètres compatibles avec la version installée).
- Augmenter progressivement le niveau PHPStan (0 -> 5 -> 8) avec baseline.
- Guarder les appels `disable('softdeleteable')` (fait) et documenter le pattern.

## 3. CI / automatisation
- Alléger `lint:all` (parallélisation, cache) et éviter les timeouts.
- Corriger la config markdownlint pour appliquer le fichier de config (fait).
- Ajouter des checks de sécurité dépendances dans la CI.

## 4. Sécurité
- Traiter les 80 alertes Dependabot (2 critiques, 41 hautes).
- Mettre à jour Symfony, Doctrine et dépendances JS vulnérables.
- Auditer les clés API dans `.env` et secrets.

## 5. Infrastructure
- Corriger Supervisor pour les logs non seekables (fait).
- Stabiliser le service messenger et la gestion des retries.
- Ajouter un healthcheck pour la stack.

## 6. Architecture
- Réduire le couplage des services IMDB et FileService.
- Clarifier les frontières entre `apps/` et `assets/`.
- Documenter les entités workflow et les flux Messenger.

## 7. Gouvernance Git
- Appliquer Git Flow (fait documenté).
- Aucun merge sans autorisation explicite (fait documenté).
- Protéger les branches `main`/`develop` sur GitHub.