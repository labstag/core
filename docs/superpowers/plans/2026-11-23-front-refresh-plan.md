# Front-end refresh — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans or superpowers:subagent-driven-development. Steps use checkbox (`- [ ]`) syntax.

**Goal:** Moderniser l'interface Labstag (dark theme) : header, hero, teasers, footer, typo, a11y, perf.

**Architecture:** Modernisation par couches sur l'existant. CSS/JS dans `assets/`. Templates Twig touchés uniquement pour sémantique HTML.

**Tech Stack:** Sass, Webpack Encore, Twig, JS vanilla (IntersectionObserver).

**Spec:** `/home/koromerzhin/.opencode/plan/2026-11-23-front-refresh-design.md`

## Global Constraints

- CSS/JS uniquement dans `assets/`. Entrée frontend : `assets/front.js`, `assets/front/index.scss`.
- Ne pas casser les classes JS : `swiper-movie`, `swiper-movie-button-next`, `swiper-movie-button-prev`, `.spoiler`, `.background`, `[data-bg]`.
- Garder les variables Sass existantes (renommage interdit).
- Git Flow : branche `feature/front-refresh-v2` depuis develop. Pas de merge main sans autorisation.
- Lint : `npm run eslint`, `npm run lint:stylelint`, build `npm run encore:dev`.

## Review Focus

1. Images de fond non chargées sans JS → fallback couleur (test manuel).
2. Nav hamburger non accessible clavier → aria-expanded + focus.
3. Contraste tags < AA → vérifier avec outil.
4. Slider sans focus visible → `:focus-visible`.
5. Debug Twig en prod → conditionner à `kernel.debug`.

---

### Task 1: Templates — sémantique HTML + debug conditionnel

**Files:**
- Modify: `apps/templates/views.html.twig`
- Modify: `apps/templates/base.html.twig`

- [ ] **Step 1: Ajouter skip-link + landmarks dans views.html.twig**

Remplacer le `<body>` pour inclure `<a class="skip-link" href="#main">Aller au contenu</a>` et `<main id="main">` autour des blocks. Conditionner `debug_begin/end` à `app.debug`.

- [ ] **Step 2: Ajouter meta description + theme-color dans base.html.twig**

Ajouter `<meta name="theme-color" content="#0f1115">` et `lang="fr"`.

- [ ] **Step 3: Vérifier le rendu**

Run: `task symfony:console CMD="cache:clear"` puis curl la page, vérifier skip-link présent.

- [ ] **Step 4: Commit**

```bash
git add apps/templates/views.html.twig apps/templates/base.html.twig
git commit -m "feat(front): semantic html + conditional debug"
```

### Task 2: Variables & base — typo, focus, reduced-motion

**Files:**
- Modify: `assets/_variables.scss`
- Modify: `assets/_base.scss`

- [ ] **Step 1: Ajouter variables texte secondaire + focus**

Dans `_variables.scss` : ajouter `$colortext-muted: #a0a8b8;` et `$radius: 14px;`.

- [ ] **Step 2: Base styles**

Dans `_base.scss` : font-family system-ui sur body, line-height 1.6, `:focus-visible` global, `@media (prefers-reduced-motion: reduce)` désactivant transitions, skip-link style.

- [ ] **Step 3: Lint style**

Run: `npm run lint:stylelint`

- [ ] **Step 4: Commit**

```bash
git add assets/_variables.scss assets/_base.scss
git commit -m "feat(front): base typography, focus, reduced-motion"
```

### Task 3: Layout & placeholders — wrapper, cards, hero

**Files:**
- Modify: `assets/_layout.scss`
- Modify: `assets/_placeholders.scss`

- [ ] **Step 1: Wrapper max-width + container**

Modifier `%wrapper` pour max-width 1200px centré, padding responsive.

- [ ] **Step 2: Hero placeholder**

Mettre à jour `%head` : overlay dégradé, titre clamp.

- [ ] **Step 3: Card placeholder**

Ajouter `%card` : border-radius, border, hover lift, transition.

- [ ] **Step 4: Lint**

Run: `npm run lint:stylelint`

- [ ] **Step 5: Commit**

### Task 4: Header links — glass nav + hamburger

**Files:**
- Modify: `assets/front/labstag.scss`
- Modify: `assets/front/js/contextmenu.js` (ou nouveau) — hamburger toggle
- Modify: `assets/front.js` — instancier

- [ ] **Step 1: CSS glass nav**

Modifier `.headerlink_principal` : sticky, backdrop-blur, nav flex, état actif, responsive hamburger.

- [ ] **Step 2: JS hamburger**

Ajouter toggle dans `front.js` (ou module dédié) avec aria-expanded.

- [ ] **Step 3: Lint JS + style**

Run: `npm run eslint && npm run lint:stylelint`

- [ ] **Step 4: Commit**

### Task 5: Teasers cards — news & stories

**Files:**
- Modify: `assets/_teasers.scss` (ou créer)
- Modify: `assets/teasers/_post.scss`, `assets/teasers/_story.scss`

- [ ] **Step 1: Card styling**

Border-radius, overlay dégradé, tags chips, hover lift, grille.

- [ ] **Step 2: Lint**

Run: `npm run lint:stylelint`

- [ ] **Step 3: Commit**

### Task 6: Hero, edito, text paragraphs

**Files:**
- Modify: `assets/_paragraphs.scss`

- [ ] **Step 1: Hero full-width overlay + CTA**

- [ ] **Step 2: Text max-width 75ch**

- [ ] **Step 3: Lint + commit**

### Task 7: Footer links

**Files:**
- Modify: `assets/_base.scss` (footer) ou `_blocks.scss`

- [ ] **Step 1: Footer grid + hover**

- [ ] **Step 2: Commit**

### Task 8: Perf — lazy images + spoiler

**Files:**
- Modify: `assets/front/js/spoiler.js`
- Modify: `assets/front/js/movie.js`

- [ ] **Step 1: IntersectionObserver pour [data-bg]**

Remplacer le chargement immédiat par IO avec fallback.

- [ ] **Step 2: Lazy slider images**

- [ ] **Step 3: Lint JS**

Run: `npm run eslint`

- [ ] **Step 4: Commit**

### Task 9: Build & verify

**Files:** none (verification)

- [ ] **Step 1: Build dev**

Run: `npm run encore:dev`

- [ ] **Step 2: Build prod (optionnel)**

Run: `npm run encore:build` (si demandé)

- [ ] **Step 3: Lint all**

Run: `npm run eslint && npm run lint:stylelint`

- [ ] **Step 4: Smoke test navigateur**

Ouvrir la page, vérifier header sticky, teasers, footer, focus-visible, reduced-motion.

### Task 10: Cleanup plan doc → docs/

- [ ] **Step 1: Déplacer design doc et plan vers docs/superpowers/**

Quand en mode build : copier `~/.opencode/plan/2026-11-23-front-refresh-*.md` vers `docs/superpowers/...` et commiter.

---

**Execution recommendation:** Subagent-driven (tâches indépendantes par fichier, interfaces claires). Ou Native si préférence. Tu choisis.