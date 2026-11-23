# Front-end refresh — Labstag

**Date:** 2026-11-23
**Status:** Approved (user)
**Scope:** frontend only (HTML/CSS/JS). Backend untouched.

## Intent

Moderniser l'interface du site Labstag (thème sombre conservé) sur trois axes :
lisibilité/contenu, design global, performance/accessibilité. Sans nouvelle
dépendance, en respectant la contrainte : CSS et JS uniquement dans `assets/`,
entrée frontend `assets/front.js` et `assets/front/index.scss`.

## Goals

- Header sticky glassmorphism + nav accessible (hamburger < md).
- Hero pleine largeur avec overlay et CTA.
- Teasers (posts/stories) en cartes modernes, grille responsive, hover lift.
- Largeur de lecture ~75ch pour le texte, hiérarchie typographique claire.
- Footer en grille de colonnes.
- A11y : skip-to-content, landmarks, `:focus-visible`, `prefers-reduced-motion`,
  contraste WCAG AA, alt textes.
- Perf : lazy-load des images de fond, suppression du debug Twig en prod,
  pas de nouvelle dépendance, font-display.

## Non-goals

- Pas de migration light mode (dark conservé).
- Pas de changement backend / entités / routes.
- Pas de refactor des templates au-delà de la sémantique HTML.
- Pas de nouvelle lib JS/CSS.

## Constraints

- CSS/JS uniquement dans `assets/`. Entrée : `assets/front.js`, `assets/front/index.scss`.
- Ne pas casser les classes existantes utilisées par le JS (`swiper-movie`,
  `swiper-movie-button-next`, `.spoiler`, `.background`, `[data-bg]`).
- Garder les variables Sass existantes (renommage interdit sans mise à jour).
- Git Flow : branche `feature/front-refresh-v2` depuis develop, pas de merge main.

## Approaches considered

1. Refonte complète from scratch (rejeté : trop risqué, casse les blocs).
2. Modernisation par couches sur l'existant (retenu).
3. Migration vers un framework CSS (rejeté : nouvelle dépendance).

## Design details

- Palette : `#0f1115` bg, `#e8e8ea` text, `#6366f1` accent, `#10b981` accent2.
  Texte secondaire `#a0a8b8` pour contraste AA.
- Typo : system-ui stack, tailles fluides via clamp(), line-height 1.6.
  Lobster conservé pour les titres (hn).
- Header : `position: sticky; top:0; backdrop-filter: blur(12px)`.
- Cartes : `border-radius: 14px`, border `#232833`, hover lift `translateY(-4px)`.
- Focus : `:focus-visible { outline: 2px solid accent; outline-offset: 2px; }`.
- Reduced motion : désactiver transitions/animations.
- Lazy : `loading="lazy"` + IntersectionObserver pour `[data-bg]` spoilers.

## Review Focus (failure modes probables)

1. Images de fond non chargées si JS désactivé → fallback couleur.
2. Nav hamburger non accessible au clavier → aria-expanded + focus trap.
3. Contraste insuffisant sur tags → vérifier AA.
4. Slider sans boutons visibles au clavier → focus-visible.
5. Debug Twig toujours présent en prod → conditionner à kernel.debug.