# Design System Brief for Claude Design

> **Audience:** Claude Design (Anthropic Labs), or any AI/human designer producing website
> designs, prototypes, or mockups **for this project**. This document teaches you the building
> blocks the site is actually made of, so the designs you produce map cleanly onto real,
> implementable units instead of arbitrary markup.

## Overview

This is a **Sulu 3, block-based CMS**. Editors do not write pages — they **compose them from
blocks**, and blocks **nest inside other blocks** to build a content hierarchy. A page is therefore
a *tree of blocks*, not a freeform canvas. The block set was designed in-house to be a robust,
opinionated toolkit for building marketing and content sites.

When you design a page for this project, you are really designing a **composition of blocks**.
The most useful design output:

1. Looks the way the brand should look (use the tokens in [§3](#3-design-tokens)).
2. Decomposes into this block tree — so note, per section, which block(s) realize it
   (see the catalog in [§5](#5-layout--composition-primitives)–[§6](#6-content-blocks-catalog)).
3. Respects the **containment rules** ([§5.4](#54-containment--nesting-graph)) — a block can only
   live where the system allows it.
4. **Flags anything that doesn't fit.** This block set is *not* a hard ceiling. If a design needs
   something no block provides, say so and describe the new block — [§11](#11-extending-the-system)
   explains exactly how, so we can extend the system rather than water down the design.

> **Working principle — existing blocks first, custom only when truly needed.** Build with the
> existing block system by **default**. It is **not a restriction**: anything the blocks can't
> express **can be programmed as a custom block or component** ([§11](#11-extending-the-system)), so
> never compromise a genuinely better design just to fit. **But custom is the exception, not the
> tool of first resort** — exhaust composition of existing blocks and utilities before proposing
> anything new. The working expectation is that **~90–95% of any design is realized with the
> existing system, and only ~5–10% is custom.** When you do go custom, keep it a small, clearly
> labeled minority of the design and describe it precisely so it can be built.

## Architectural overview

Four layers cooperate to render a page. You mostly design against the **first** layer, but knowing
all four tells you what is cheap, what is expensive, and what is dynamic.

| Layer | What it is | Relevance to design |
|---|---|---|
| **CMS blocks** | Server-rendered Twig, composed as a nested tree by editors | The primary design surface — every section is one or more blocks |
| **Stylesheets** | SCSS compiled per webspace; shared token base + utility classes | Defines the visual language — colors, type, spacing, grid ([§3](#3-design-tokens)–[§4](#4-stylesheet-structure--class-vocabulary)) |
| **Vue "islands"** | Small Vue apps mounted onto the server HTML for interactivity | Galleries, forms, cookie/toast UI — the dynamic parts ([§7](#7-dynamic-components-vue-islands)) |
| **API Platform** | Custom Symfony backend endpoints for data in/out | Form submissions, lead capture, consent logging ([§8](#8-data-backed-capabilities-api-platform)) |

A project has **one or more webspaces** — from a single site up to an unbounded number. Each is an
independent site (own domain, localizations, navigation) built on a shared common base. A webspace
can **share almost everything** and differ only in a few brand tokens, or be **fully independent**.
Understand this "shared vs. independent" model early
([§2](#2-webspaces--the-shared-vs-independent-model)) — it decides whether a design choice is made
once or many times.

---

## 1. Mental model — think in blocks

- **Everything is a block.** Headings, images, buttons, whole hero sections, galleries, forms —
  all blocks.
- **Blocks nest.** Layout blocks (background, grid, flex, card) are *containers*; content blocks
  (text, image, button, video…) are *leaves*. A page is a tree: page → top-level blocks →
  their children → their children.
- **Composition, not custom CSS.** Prefer arranging existing blocks over inventing new visuals.
  Spacing, columns, alignment, and color are almost always achieved by choosing block options and
  utility classes — not by bespoke styling.
- **Containment is constrained.** Each container permits only a specific set of child block types
  ([§5.4](#54-containment--nesting-graph)). Design within that graph, or propose an extension.
- **Named UI patterns are *emergent*, not dedicated blocks.** The blocks are deliberately
  **general-purpose primitives**, so familiar components arise from *composing and configuring*
  them — there is no literal "hero block," "image-link block," or "banner block." For example:
  - a **hero** = a `background-block` (with `hero`, decorative background layers) wrapping a
    `grid`/`flex` of heading + buttons;
  - an **image link with overlaid text** = a `card-block` with a `link` + a `background` image and
    text/button children stacked on top;
  - a **CTA band**, **feature grid**, **split media/text**, etc. are all just arrangements of the
    same primitives (see the recipes in [§9](#9-composition-patterns--emergent-components)).
  When you see a pattern, map it to *which primitives compose it*, not to a block of that name.
- A block is a real, implementable unit wired across ~6 points (Sulu XML field definitions, a Twig
  template, SCSS, optional TS, the `content-loop` dispatcher, and a `type-ref` registration). You
  don't produce those — but it means every block, **existing or newly programmed**, is a concrete
  buildable unit. Treating each design section as "one block" (or a small composition of them) keeps
  designs buildable.

---

## 2. Webspaces & the shared-vs-independent model

A project can have **any number of webspaces — one, two, or many** — all edited from a single admin.
A webspace is a self-contained site (its own domain, localizations, navigation, page tree, and its
own **website-config snippet** carrying header/footer/branding data — so those can differ per
webspace) built from a shared common asset base. **The number and identity of webspaces is
project-specific; don't assume two.** Ask (or infer) which webspace(s) a design targets.

**This init repo ships two example webspaces to demonstrate the model — `website` and `campaign`.
They are illustrative, not fixed:**

| | `website` (example) | `campaign` (example) |
|---|---|---|
| Role | Main content site | A focused campaign/landing site |
| Theme | **Blue** (default tokens) | **Green** (overridden tokens) |
| Vite entry | `assets/website/app.ts` / `app.scss` | `assets/campaign/app.ts` / `app.scss` |
| Navigation | header, footer1, footer2, footer-legal | header, footer-legal (leaner) |
| Extra JS | sticky-header + carousel behavior | (omits those) |

**Sharing is a spectrum — this is the crux:**

- **Fully shared (default).** A webspace `@use`s everything from `assets/common/` (all blocks,
  layout, typography, spacing, utilities, Vue apps) and overrides only a small **brand token set**
  in its one local file `assets/<webspace>/styles/base/variables.scss`. In this repo, `website`
  overrides *nothing* (pure defaults); `campaign` overrides **7 tokens** (see
  [§3.1](#31-brand-theme-tokens-per-webspace)). The overridable levers are the brand colors **and the
  font** (`$defaultFont`).
- **Fully independent.** A webspace *can* diverge arbitrarily — its own SCSS/JS tree, its own
  structure — if a project genuinely needs unrelated sites under one codebase.
- **Anywhere in between.** Share structure, override more tokens, add webspace-only styles.

**Design implications:**

- First establish **how many webspaces exist and which this design is for.** With multiple
  webspaces that share structure, **design once and re-theme** per webspace via brand tokens
  (color + font); don't invent divergent layouts unless asked.
- If a webspace must diverge *beyond* tokens, say so — that's either a new shared option or genuinely
  independent styling ([§11](#11-extending-the-system)).

---

## 3. Design tokens

All tokens are **SCSS variables** in `assets/common/styles/base/variables.scss` (declared `!default`
so a webspace can override them). There are **no CSS custom properties** — theming is compile-time.
`rem` is root-relative (root = 16px); body text renders at `0.875rem` (14px).

> **Sizing rule — use `rem` for everything.** All dimensions — font sizes, spacing, widths,
> heights, gaps, radii, container/max-widths, breakpoints — **must be expressed in `rem`**, never
> `px`. The **only** exception is **thin/hairline borders**, which are specified in `px` (e.g. a
> `1px` rule line / `hr`). Every token and utility in this system already follows this; any new
> value you introduce should too. Do not hand back designs measured in pixels.

### 3.1 Brand theme tokens (per webspace)

These seven are the entire brand surface that differs between webspaces. Design with the correct
column for the target webspace.

| Token | Role | `website` (blue) | `campaign` (green) |
|---|---|---|---|
| `$primaryColor` | Primary brand / CTAs | `#1B5E8A` | `#3F785E` |
| `$contrastToPrimaryColor` | Text on primary | `#FFFFFF` | `#FFFFFF` |
| `$accentColor` | Accent / highlights | `#5BB8D4` | `#4CF5A8` |
| `$contrastToAccentColor` | Text on accent | `#0D2B3E` | `#1A1A1A` |
| `$backgroundSoftColor` | Soft section background | `#EBF5FB` | `#F0F5F2` |
| `$footerBackgroundColor` | Footer background | `#0D2B3E` | `#1A2E25` |
| `$contrastToFooterBackgroundColor` | Text on footer | `#D6EAF8` | `#D4EDE3` |

### 3.2 Fixed palette (shared, both webspaces)

| Token | Value | Use |
|---|---|---|
| `$textColor` | `#555555` | Body text (`= $darkGrey`) |
| `$textHighlightColor` | `#000000` | Headings, emphasized text |
| `$grey` | `#6A6969` | Muted / highlight text |
| `$darkGrey` | `#555555` | — |
| `$background` | `#FFFFFF` | Page background |
| `$cardBackground` | `#F2FAFC` | Card surface (`= $lightBlue`) |
| `$lightGrey` | `#F0F5F2` | Soft neutral |
| `$lightBlue` | `#F2FAFC` | Soft neutral |
| `$green` / `$lightGreen` | `#3F785E` / `#4CF5A8` | Base green pair |
| `$btnHover` | `#1A1A1A` | Button hover |
| `$boxShadow` | `0 1.125rem 2rem #1111158C` | Card/elevated shadow |
| `$btnShadow` | `0 .5rem 1rem #11111533` | Button shadow |
| `$lineColor` | `$grey` @ 16% alpha | Hairlines, `hr` |

> **Dark theme** tokens exist (`$darkBackground #151515`, etc.) but the `darktheme.scss` files are
> currently empty — there is **no working dark mode**. Treat dark mode as an extension.
> Individual sections *can* go dark via the `dark-section` / `darkSection` option on
> background-block and card-block (white text on a dark surface).

### 3.3 Typography scale

**Font is not fixed — choose freely.** The init repo defaults to **Roboto**
(`$defaultFont: "roboto", Arial, sans-serif`, weights 100/300/400/700/900) purely as a placeholder;
it is **not a requirement**. The font is a single overridable token (`$defaultFont`), set globally or
per webspace, so pick whatever typeface suits the brand. What matters and stays constant is the
**type scale, weights, and line-heights** below (the *shape* of the hierarchy), not the specific
family. Headings are black (`#000`) and centered by default (`margin: 0 auto`); body is `#555555`,
capped at `$textMaxWidth: 50rem` for readability. Sizes are responsive at `tablet` (55rem) and
`desktop` (73rem).

| Element | Mobile | Tablet | Desktop | Weight | Line-height |
|---|---|---|---|---|---|
| `h1 / .h1` | 2.25rem | 3rem | 4rem | 300 | 100% |
| `h2 / .h2` | 1.6rem | 2rem | — | 700 | 100% |
| `h3 / .h3` | 1.4rem | 1.7rem | — | 700 | 100% |
| `h4 / .h4` | 1.1rem | 1.5rem | — | 700 | 150% |
| `h5 / .h5` | 1rem | 1.25rem | — | 700 | 150% |
| `p` (body) | 1rem | — | — | 300 | 150% |
| `p.large` | 1.5rem | 2rem | — | 300 | — |
| `p.medium` | 1.125rem | 1.5rem | — | — | — |
| `p.small` | 0.82rem | — | — | — | — |

Text helpers: `.highlight` (muted heading / black `p`), `.accent` (accent color),
`mark.pen-primary` / `mark.pen-accent` (highlighter effect in brand colors),
`.text-primary` / `.text-secondary`, and alignment utilities in [§4](#4-stylesheet-structure--class-vocabulary).

### 3.4 Spacing, sizing, breakpoints

| Token | Value | Meaning |
|---|---|---|
| `$pageMaxWidth` | `168rem` | Absolute page cap (full-bleed sections) |
| `$containerMaxWidth` | `120rem` | Standard content container width |
| `$textMaxWidth` | `50rem` | Max readable text column |
| `$borderRadius` | `2rem` | Large radius (cards, images) |
| `$btnBorderRadius` | `.3rem` | Button radius |
| `$gap` | `1rem` | Base grid gap |
| Container gutter | `1.5rem` → `2rem` → `3.5rem` → `5rem` | Padding at mobile / 55rem / 73rem / 100rem |
| `$gapBase` / `$paddingBase` | `.5rem` | Multiplier for `.gap-N` and `.mX-N` / `.pX-N` utilities |
| `$btnAngle` / `$greenLineAngle` | `-17deg` / `-27deg` | Skew used by buttons / decorative lines |

**Breakpoints (mobile-first):** `$tablet: 55rem` (~880px), `$desktop: 73rem` (~1168px).
Map `$breakpoints: (mobile: 0, tablet: 55rem, desktop: 73rem)`. Utilities and type scale key off
these three. (A few components hardcode extra breakpoints — 70rem nav, 100rem gutter, 65rem popup.)

---

## 4. Stylesheet structure & class vocabulary

`assets/common/styles/` is organized into `base/`, `components/`, `layout/`, `blocks/`, `fonts/`.
Both webspace entries import the same set. For design alignment, the important part is the **utility
class vocabulary** — designs should be expressible in these classes.

- **`base/`** — `variables` (tokens), `resets`, `typography`, `display` (display + flex utils),
  `margins` (spacing utils), `errors`.
- **`components/`** — `btn`, `card`, `carousel-navigation` (reusable UI surfaces).
- **`layout/`** — `container`, `row` (the grid), `flex`, `header`, `header-menu`, `footer`,
  `welcome-popup`.
- **`blocks/`** — one file per styled block.
- **`fonts/`** — empty; fonts come from `@fontsource/roboto` in JS.

**Tooling:** modern Dart-Sass `@use`/`@forward` modules, no `@import`, no mixins/functions, native
`@media (min-width)` and `color-mix()`. Utilities are generated with `@each`/`@for` loops and are
**mobile-first** with `!important`.

### 4.1 The utility classes to design with

| Group | Classes | Notes |
|---|---|---|
| **Container** | `.container` | Centers, `max-width 120rem`, responsive gutters |
| **Grid** | `.row` + `.col-1…14`, `.col-tablet-*`, `.col-desktop-*`, `.col-start-*`, `.row-1…5`, `.order-*`, `.gap-0…7`, grid variants `.row-10` / `.row-14` | 12-col CSS grid by default; see [§5.1](#51-grid-block--the-column-system) |
| **Flex** | `.d-flex` + `.gap-0…3`, plus `.wrap`/`.nowrap`/`.column`, `.align-*`, `.justify-*` (each with `-tablet`/`-desktop`) | Inline arrangement |
| **Display** | `.d-block/-flex/-grid/-none` (+ `-tablet`/`-desktop`) | Responsive show/hide |
| **Spacing** | `.pa/.px/.py/.pt/.pr/.pb/.pl-N` and `.ma/.mx/.my/.mt/.mr/.mb/.ml-N` (+ responsive, + `-auto`) | Step = `N × .5rem`; margins allow negatives |
| **Text** | `.text-center/-left/-right` (+ responsive), `.text-block` (50rem centered), `.strong`, `.text-primary`, `.text-secondary` | — |

> **Gotcha — gaps collapse on mobile.** In `.row`, `.gap-2`…`.gap-7` all resolve to **1rem** on
> mobile and only spread out at tablet/desktop (`.gap-5` = 5rem only ≥73rem). Don't rely on large
> gaps for mobile layouts.

---

## 5. Layout & composition primitives

These are the containers you build sections with. Each block's **options** below are the real,
editor-configurable choices — design within them.

### 5.1 grid-block — the column system

The main multi-column layout block. Renders `<div class="row …">` with child columns.

- **Container options:** `align` (`align-center` default / `-start` / `-end` / `-stretch` /
  `-stretch-first` / `-stretch-last`), `gap` (`gap-0…gap-5`, default `gap-1`),
  `colWidth` (grid columns: default **12**, `row-10` = 10, `row-14` = 14), `dense`, `visualise`
  (debug), `classes`.
- **Per-column options:** `col` span (`col-1…14` + `-tablet-*` + `-desktop-*`, default `col-12` =
  full width), `order` (`order-tablet/desktop-1…12`), `row` (rowspan `row-1…5` + responsive),
  `colStart` (`col-start-*` + responsive), `classes`.
- **Allowed children (per column):** text, image, flex, button, card, divider, empty, form, video,
  carousel-gallery, carousel-reference. *(masonry-gallery is declared but not yet rendered — see
  [§10](#10-constraints--gotchas).)*

Use grid-block for anything with distinct columns: feature rows, card grids, split hero, media +
text, footers-within-content.

### 5.2 flex-block — inline arrangement

Flexbox row/wrap for small elements. Renders `<div class="d-flex …">`.

- **Options:** `justify` (`center` default / `start` / `end` / `space-between` / `space-around`),
  `align` (`center` default / `baseline` / `start` / `end`), `gap` (`gap-0`, default .5rem,
  `gap-1`, `gap-2`, `gap-3`), `wrap` (`no-wrap` default / `wrap`), `classes`.
- **Allowed children:** button, text, image. *(a `line-block` divider is declared but not yet
  rendered.)*

Use flex-block for button groups, inline badges, small icon+text rows.

### 5.3 background-block — the section wrapper

The primary **full-bleed section** primitive: a layered decorative/image background behind a
contained foreground. Renders `<div class="background-block …"><div class="wrapper-content container">…`.

- **Options:** `hero` (hero sizing), `darkSection` (dark surface → white text), `classes` /
  `contentClasses`, `contentAspectRatio`.
- **Two child regions:**
  - **`background`** — decorative layers (rendered back-to-front): responsive positioned
    `image-block`, `grey-block` (default/primary/accent color panels), `grill-block`,
    `green-line-block` (skewed accent lines), `container-image-block`. All support per-breakpoint
    left/top/width/height. This is how bespoke, layered section backgrounds are built.
  - **`content`** — foreground blocks: grid, flex, text, button, image, divider, empty,
    carousel-reference.

Use background-block for every distinct **full-width section** with a colored/image backdrop or a
hero. It is the top-level section wrapper; grid/flex go *inside* its content region.

### 5.4 Containment / nesting graph

**This is the rule that keeps designs buildable.** A block may only contain the child types listed.

| Container | Allowed children |
|---|---|
| **Page root** (`content`) | text, image, grid, background, content-snippet, flex, button, video, scrollable-video, card, carousel-gallery, divider, empty, form, generic-header, masonry-gallery, newsletter-subscribe, timeline |
| **background-block** → `background` | image, grey-block, grill-block, green-line-block, container-image-block *(decorative layers)* |
| **background-block** → `content` | grid, flex, text, button, image, divider, empty, carousel-reference |
| **grid-block** → column `content` | text, image, flex, button, card, divider, empty, form, video, carousel-gallery, carousel-reference |
| **flex-block** → `content` | button, text, image |
| **card-block** → `content` | button, text, image, flex, video |
| **button-block** → `content` | text, image |
| **timeline-block** → `events` | event (year + text + image) |
| carousel-gallery / carousel-reference / masonry-gallery | image / reference / image leaves only |
| content-snippet-block | re-renders a "content" snippet (full page-root set) |
| newsletter-subscribe-block | embeds a form-block |
| **Leaf blocks** (no children) | text, image, divider, empty, form, generic-header, video, scrollable-video, content-snippet |

**Full-bleed vs. contained:** the page wraps most top-level blocks in `.container` (max 120rem), but
renders these **edge-to-edge**: background, content-snippet, scrollable-video, carousel-gallery,
empty, masonry-gallery, newsletter-subscribe. So: full-width sections → background-block (or the
full-bleed blocks); everything else is capped at container width.

---

## 6. Content blocks catalog

Leaf and specialized blocks, with their design-relevant options.

| Block | Purpose | Key options |
|---|---|---|
| **text-block** | Rich-text (WYSIWYG) | `text` (HTML), `noPaddings`, `classes`. Body capped at 50rem. |
| **image-block** | Responsive image | per-breakpoint `image`/`imageTablet`/`imageDesktop`, `aspectRatio`, `maxWidth`/`maxHeight`, `roundCorners`, `outline`, `registerToGallery` (→ lightbox) |
| **button-block** | CTA button / link | `text`, `link` (+ `anchor`, `link_target`), `size` (default/small/large), `style` (default/outline/fill/circle), `color` (default/white/black/primary/accent), `flexDirection` |
| **card-block** | Content card, optional bg image + whole-card link | `background`(+Tablet), `aspectRatio`, `link`, `darkSection`, `classes`; holds button/text/image/flex/video |
| **divider-block** | Vertical spacer | `height` (0–20rem) |
| **empty-block** | Invisible aspect-ratio spacer | `aspectRatio` + Tablet + Desktop (responsive spacing) |
| **generic-header-block** | Page header: breadcrumb + H1 + perex | `titleOverride`, `classes` (pulls page title/perex) |
| **video-block** | Inline video player | per-breakpoint sources, `placement` (contain/cover), `playback` (paused/playOnce/loop/sectionLoop), `aspectRatio`, sizing, `showUi` |
| **scrollable-video-block** | Scroll-scrubbed background video | per-breakpoint sources, `animationLength`, `hero`, `startOffset`/`endOffset` (full-bleed, JS-driven) |
| **carousel-gallery-block** | Horizontal image carousel | `images` (each: image + `outline`), `aspectRatio`, dot/arrow nav (full-bleed) |
| **carousel-reference-block** | Testimonial/quote carousel | `references` (each: text + name + about) |
| **masonry-gallery-block** | Masonry image grid | `images` (each: image + `large` double-tile) (full-bleed) |
| **timeline-block** | Vertical dated timeline | `events` (each: year + rich text + image) |
| **form-block** | Vue-rendered form from a form snippet | `ctaText`, `ctaColor`, `ctaInline`, `formTemplate` (snippet). See [§7](#7-dynamic-components-vue-islands)/[§8](#8-data-backed-capabilities-api-platform) |
| **newsletter-subscribe-block** | Intro text + embedded form | `text` (rich), `ctaText`/`ctaColor`/`ctaInline`, `formTemplate` (full-bleed) |
| **content-snippet-block** | Embeds a reusable "content" snippet | `contentSnippet`, `classes` (full-bleed) |

**Page-level controls** (not blocks, but design-relevant): every page has `heroHeader` and
`darkHeroBackground` (white header text) toggles, an SEO `ogImage`, and a "page card"
(`displayTitle` / `perex` / `coverImage`) that governs how the page appears as a **link card** in
listings. The **article** template adds `image`, `perex`, and a long-form `article` rich-text field.
Global header/footer content comes from a **`websiteConfig`** snippet, and an optional site-wide
**welcome popup** snippet.

---

## 7. Dynamic components (Vue "islands")

Interactivity is added by small Vue apps mounted onto the server-rendered HTML at fixed selectors
(no SPA). Design *for* these behaviors; the components themselves are fixed unless extended.

| Component | Trigger / mount | Behavior — design hook |
|---|---|---|
| **Gallery lightbox** | any element with `data-gallery="<full-img-url>"` (image-block `registerToGallery`) | Fullscreen swipeable lightbox with keyboard nav. Design the trigger thumbnails; overlay is standard. |
| **Form renderer** | `.vue-form-block` (inside form-block) with a JSON config | Schema-driven form: text/textarea/select/checkbox/switch fields, client validation, honeypot, GDPR notice, loading + success states. Uses **Vuetify**. Field set, columns, CTA text/color/inline, GDPR link all config-driven. |
| **Cookie bar** | `#vue-cookie-bar-app` | Bottom consent banner (analytics-only / none / all), pushes GTM consent event. |
| **Cookie settings** | any `[data-cookie-settings-trigger]` (e.g. footer legal link) | Vuetify dialog for granular re-consent. |
| **Toast bar** | `#vue-toast-bar-app` | App-wide notifications (info/primary/success/error), persist across reload. Any component can raise one. |
| **Carousel** | `.carousel` markup | Scroll-snap slider with dot/arrow nav (used by the gallery/reference blocks). *website only.* |
| **Sticky header / mobile menu** | `header` markup | Sticks on scroll, hamburger menu. *website only.* |
| **Welcome popup** | `.welcome-popup-dialog` (from snippet) | Once-per-visitor modal (localStorage-dismissed). |

Localization for all app strings exists in **en / cs / de / sq**. Vuetify is only used by the two
form/dialog apps.

---

## 8. Data-backed capabilities (API Platform)

Two custom backend endpoints exist. A design can rely on these data flows; anything beyond them is
an extension.

- **Contact / newsletter form → Lead.** `POST /api/contact_form` with `{ formUUID, locale,
  formData, hp }`. The **form schema lives in a Sulu "form" snippet** (fields, target email, GDPR
  link, and whether it's a `contactForm` or `newsletterSubscription`). On submit: validates against
  the snippet schema, emails staff (+ optional confirmation email to the submitter), and persists a
  **Lead** (name, email, phone, message, raw payload) shown in the Sulu admin. Recognized fields
  include email, phone, name, message, plus org/address fields.
- **Cookie consent.** `POST /api/cookie_consent` with `{ preferences: ['analytics','marketing'] }`;
  logs consent (with IP, user-agent, timestamp) and drives GTM consent events.

There is **no** cart/e-commerce, user accounts, or other custom data API. If a design needs
persisted data, dynamic content from the backend, search, filtering, etc., treat it as an extension
([§11](#11-extending-the-system)).

---

## 9. Composition patterns & emergent components

Familiar UI components are **not dedicated blocks** — they *emerge* from composing the primitives.
The trees below are the canonical way to build each; treat them as the default mapping from a named
pattern to real blocks.

- **Hero.** `background-block` (`hero`, optional `darkSection`, decorative `background` layers) →
  `content`: `grid-block` or `flex-block` with `text-block` (h1 + perex) + `button-block`(s).
  *(There is no "hero block" — a hero is this composition.)*
- **Image link with overlaid text** (a.k.a. clickable image tile). `card-block` with a `link` +
  `background`/`backgroundTablet` image + `darkSection` (for legible white text), holding a
  `text-block` (+ optional `button-block`) stacked over the image. Set `aspectRatio` for a
  consistent tile shape.
- **Feature grid / card row.** `grid-block` (`colWidth` 12, `gap-*`) → columns (`col-desktop-4`
  etc.) each holding a `card-block` (bg image + text + button) — i.e. a row of the image-link tiles
  above, or plain content cards.
- **Media + text split.** `grid-block` two columns (`col-desktop-6`), one `image-block`/`video-block`,
  one `text-block` + `button-block`; use `order-*` to flip on mobile.
- **Full-width colored band / CTA.** `background-block` (`darkSection` or `grey-block` layer) →
  centered `text-block` + `flex-block` of buttons.
- **Image gallery.** `carousel-gallery-block` or `masonry-gallery-block`; set `registerToGallery` on
  images for lightbox.
- **Testimonials.** `carousel-reference-block` (quote + name + about), often inside a
  `background-block` section.
- **Timeline / history.** `timeline-block` (year + text + image per event).
- **Contact / newsletter.** `form-block` (bind a "form" snippet) or `newsletter-subscribe-block`
  (intro text + form).
- **Reusable section across pages.** Build once as a "content" snippet, embed via
  `content-snippet-block`.

---

## 10. Constraints & gotchas

- **12-column grid** by default (`.row`), with `row-10` / `row-14` variants; columns responsive at
  tablet (55rem) / desktop (73rem).
- **Mobile-first**, `min-width` breakpoints only. No max-width media queries in the utility system.
- **Grid gaps collapse on mobile** — `.gap-2…7` are all 1rem below 55rem ([§4.1](#41-the-utility-classes-to-design-with)).
- **SCSS-only theming**, no CSS custom properties, no runtime theme switching.
- **No working dark mode** (empty `darktheme.scss`); per-section dark is only via `dark-section`.
- **Full-bleed vs. contained** matters — see [§5.4](#54-containment--nesting-graph). Standard content
  is capped at 120rem.
- **Declared-but-not-rendered children** (avoid until wired): `masonry-gallery-block` inside a
  grid column, and `line-block` inside a flex-block — both currently fall through to an error box.
  If a design needs them, note it as a small wiring extension.
- **Headings and body are centered / max-width capped** by default (headings `margin: 0 auto`, body
  50rem). Left-align with `.text-left`.

---

## 11. Extending the system

**This block set is a starting toolkit, not a hard limit — and not a cage.** Anything the blocks
can't express **can be programmed custom**. So when a design genuinely needs something new, **do not
distort the design to fit** — design it properly and **describe the extension** with enough detail to
build.

**Existing-first is the expectation.** Compose existing blocks and utility classes by default; reach
for custom only when (a) no combination of existing blocks produces the visual/behavior, or (b) the
pattern will recur and deserves to be a first-class block. Target **~90–95% of every design built on
the existing system, ~5–10% custom**, and keep the custom parts a clearly labeled minority.

**What "custom" costs (so you can weigh the trade-off).** A new block is wired across ~6 points — a
Sulu XML field definition, a Twig template (the DOM/class source of truth), SCSS, optional TS
behavior, an entry in the `content-loop` dispatcher, and a `type-ref` registration in each block
collection that should offer it. Interactive behavior becomes a Vue island; persisted or dynamic
data becomes an API Platform endpoint. None of this is expensive when described well — but it is real
work, which is exactly why composition of existing blocks comes first.

**How to describe a new block** — give us these, matching the four-layer anatomy:

1. **Name & purpose** — one line.
2. **Where it lives** — which container(s) it should be allowed inside, and which child block types
   (if any) it should accept. (Extending an existing container's allowed-types list is often all
   that's needed.)
3. **Editor options** — the configurable fields (text, select + option values, image, checkbox,
   color, nested blocks…), with defaults. These become Sulu XML fields.
4. **DOM & classes** — the intended markup and class names (reuse the existing token/utility
   vocabulary where possible). This becomes the Twig + SCSS.
5. **Behavior** — if interactive, describe it; it becomes a Vue island (mount selector +
   config) or a small vanilla-TS module.
6. **Data** — if it reads/writes backend data, describe the shape; it becomes an API Platform
   resource + processor ([§8](#8-data-backed-capabilities-api-platform)).

**Common extension shapes, cheapest first:**

- **New color/size option** on an existing block (add a select value).
- **New allowed child** in a container (widen the containment graph).
- **New leaf block** (self-contained visual, no children).
- **New container block** (defines its own child region + allowed types).
- **New Vue island** for interactivity, or **new API endpoint** for data.
- **New brand token** shared across webspaces, or a genuinely **independent webspace theme**.

When in doubt, output the design as intended and annotate the parts that need extension — clarity
about the gap is more valuable than forcing everything into existing blocks.

---

## Quick reference

- **Page = tree of blocks.** Sections are blocks; blocks nest per the graph in [§5.4](#54-containment--nesting-graph).
- **Named patterns emerge from primitives** — no "hero"/"image-link"/"banner" block; compose them
  ([§9](#9-composition-patterns--emergent-components)).
- **Section wrapper:** `background-block` (full-bleed). **Columns:** `grid-block` (12-col).
  **Inline:** `flex-block`. **Card / image-link:** `card-block`.
- **Webspaces:** 1..N per project (`website`/`campaign` here are just examples); shared structure,
  per-webspace brand tokens (7 here) + font.
- **Sizing:** **`rem` for everything**, `px` only for thin borders.
- **Tokens:** font is free (Roboto is a placeholder default); primary/accent per webspace; container
  120rem; breakpoints 55rem/73rem; spacing step .5rem.
- **Dynamic:** gallery lightbox, schema-driven forms (→ leads), cookie/consent, toasts.
- **Existing-first:** build **~90–95% from existing blocks**; custom is allowed (nothing is
  off-limits) but the exception — design it right and describe the extension
  ([§11](#11-extending-the-system)).
