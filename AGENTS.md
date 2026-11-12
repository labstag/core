# AGENTS.md

Symfony 8.1 application (`apps/`) plus a Webpack Encore frontend (root `assets/`). PHP code lives in `apps/src` (PSR-4 `Labstag\`); tests in `apps/tests` (PSR-4 `Labstag\Tests\`).

## Layout

- `apps/src` — application code (controllers, entities, services, forms, Twig extensions, etc.)
- `apps/tests/{Unit,Integration,Functional,Performance}` — PHPUnit suites
- `apps/templates`, `apps/config`, `apps/migrations` — views, Symfony config, Doctrine migrations
- `assets/` (root) — JS/SCSS entrypoints compiled by Encore into `apps/public/build`
- `taskfiles/` — git submodule (task runner includes); `lampy/` — external infra repo (not in this repo)

## Environment

- Copy `apps/.env.example` → `apps/.env` and root `.env.example` → `.env`, then adjust.
- Root `.env` drives the stack: `DOCKERCOMPOSEFILE`, `FOLDERLAMPY`, `SERVERNAME`, `STACK`, `UID`/`GID`. Do not assume the defaults in `Taskfile.yml`.
- The full stack needs the `lampy` repo at `FOLDERLAMPY` and the `taskfiles` submodule.

## Commands

All PHP/Symfony work runs inside the Docker `www` service via `task`.

Dev stack (heavy: pulls images, deploys swarm):

- `task labstag:exec`

Dependencies:

- `npm install`
- `task labstag:install-first` (composer install in container)
- `task composer:exec CMD="install"`

Assets:

- `npm run encore:dev` / `npm run encore:watch` / `npm run encore:build`

Symfony:

- `task symfony:console CMD="cache:clear"`
- `task doctrine:migrate`
- `task doctrine:schema:update`
- `task doctrine:schema:validate`
- `task doctrine:fixtures`

Tests (run after `task labstag:exec`):

- `task test:setup` (removes `var/test.db`, creates schema + fixtures)
- `task test:unit` / `test:integration` / `test:functional` / `test:performance`
- `task test:all` (setup + all suites + cleanup)
- `task test:cleanup`

Single test:

- `task labstag:cmd-exec COMMAND="php bin/phpunit --filter 'TestName' tests/Unit/..." INTERACTIVE=false`

Lint / quality:

- `task lint:all`
- `task quality:all` (lint + tests + phpstan level 5)
- `task quality:ci` (phpaudit + unit tests + phpstan level 8)
- `task phpstan LEVEL=5`
- `npm run eslint` / `npm run lint:stylelint`
- `task fix:all`

## Testing quirks

- Tests use SQLite at `apps/var/test.db` (forced in `phpunit.xml.dist`); `.env.test`'s `var/behat.db` is not what PHPUnit uses.
- Run `task test:setup` before any suite; DAMA Doctrine Test Bundle wraps tests in transactions.
- Functional/performance tests may require the app reachable at `SERVERNAME`.
- Local system PHP is 8.2, but the app requires `>=8.4`; use the container's PHP (8.5) via `task`.

## Architecture notes

- Services are autowired from `src/` (`config/services.yaml`), except entities.
- Workflow-enabled entities: chapter, edito, story, memo, post, page, user.
- Frontend source is at repo root `assets/`, not under `apps/`.

## Git

- Always use Git Flow (feature/develop/release/hotfix branches); never commit directly to `main`.
- Never merge or push a merge without explicit user authorization.

## Gotchas

- `task labstag:exec` and tests require the external `lampy` stack and `taskfiles` submodule.
- `task labstag:cmd-exec` needs the stack running (`task labstag:deploy`).
- `phpunit.xml.dist` forces `APP_ENV=test` and `DATABASE_URL=sqlite:///.../var/test.db`.
