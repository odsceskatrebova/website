# Plan: ODS design blocks (stripes, seat-progress, project-map, image placeholder)

## Context

ODS (`odsct` webspace) is a Sulu 3 site copied from the `sulu-init-3` bootstrap. The
approved design (`design/ODS Web odsct.cz (Sulu).dc.html`, gitignored but on disk —
READ IT for visual reference at the line numbers each task cites) needs three new
blocks and one image-block improvement. The report-map feature is explicitly OUT of
scope (placeholder content only, nothing to build).

A block in this repo has 6 wiring points (see the sulu-3 skill,
`~/.claude/skills/sulu-3/references/porting-blocks.md`):
XML in `config/templates/blocks/`, Twig in `templates/blocks/`, SCSS in
`assets/common/styles/`, optional TS, the `content-loop.html.twig` dispatcher
(top-level blocks only), and `<type ref>` registrations.

Composable background elements (`color-block`, `glow-block`, `line-block`) are the
established pattern for section backgrounds: leaf XML with a `<key>`, a `_name.html.twig`
partial included from `background-block.html.twig`'s background loop, SCSS in
`assets/common/styles/components/`. Model new work on `glow-block` +
`_glow.html.twig` + `components/glow.scss` — read those three files first.

## Global Constraints

- Common-layer files (`assets/common/`, `config/templates/blocks/`, `templates/blocks/`)
  must stay **webspace-agnostic** — no `odsct` tokens in them.
- Every new stylesheet must be `@use`d from `assets/odsct/app.scss` (the only webspace).
- Colors come from existing SCSS tokens in `assets/common/styles/base/variables.scss`
  (`$brand`, `$indigo`, `$navy`, `$border`, `$primaryColor`, `$accentColor`, …), never
  hard-coded hex in component SCSS. New tokens must be `!default`.
- All sizing in rem; free-text size fields pass through as raw CSS values (the
  existing glow/color blocks show the pattern).
- XML field conventions: lower-case titles, `sulu-display-name` (colspan 3, preview tag
  priority 512) + `id` (colspan 3) + `classes` (colspan 6) header, as in the other blocks.
- Commit per task using the house format (invoke the `git-commits` skill): lower-case
  prefix (`ftr:`), imperative, context `blocks:` or `image-block:`. NEVER add
  Co-Authored-By/AI attribution. Commit ONLY the files this task touches — the working
  tree has unrelated pre-existing modifications (`.gitignore`, `package-lock.json`,
  `templates/bundles/…/seo.html.twig`) that must NOT be staged.
- Verification: if the `asset-builder` container is running, run
  `docker compose exec asset-builder npm run tsc` and `… npm run lint`
  (SCSS compile is covered by `npm run build` — run it if tsc+lint pass quickly).
  If the container is not running, do static verification (every `@use` target exists,
  every Twig include target exists, XML has `<key>`) and state clearly in your report
  that toolchain verification was skipped. Admin cache:clear is deferred (stack down).

## Task 1: stripes-block background element

New composable background element (successor of the historical "grill" element).
Design usages — read these lines in the design file:
- line 140: bilance section, full-cover subtle white wash on dark blue —
  `repeating-linear-gradient(-55deg, rgba(255,255,255,.03) 0 4.375rem, transparent 4.375rem 8.75rem)`
- line 170: díra section, full-cover subtle accent wash —
  `repeating-linear-gradient(-55deg, rgba(242,140,30,.09) 0 2.875rem, transparent 2.875rem 5.75rem)`
- line 206: makat section, **hazard strip**: positioned bar (top:0, height:1.625rem),
  two-color alternating `repeating-linear-gradient(-45deg, #f2c81e 0 1.375rem, #14181f 1.375rem 2.75rem)`

Files to create:
- `config/templates/blocks/stripes-block.xml` — key `stripes-block`. Properties
  (model field layout on `glow-block.xml`):
  - `variant` single_select (colspan 3, preview tag priority 512): `wash` (default) | `hazard`
  - `stripeColor` single_select (colspan 3): `white` (default) | `brand` | `accent` | `warning` | `dark`
    (wash renders the color at low alpha; hazard alternates the color with `$navyDeep`-style dark)
  - `angle` text_line colspan 3 (blank = -55deg for wash, -45deg for hazard)
  - `stripeWidth` text_line colspan 3 (blank = 4.375rem wash / 1.375rem hazard)
  - `opacity` text_line colspan 3 (blank = variant default)
  - position box like glow-block: `top`/`left`/`right`/`bottom`/`width`/`height`
    text_line colspan 2 each — all blank = full cover (inset 0)
  - `depth` text_line colspan 3 (parallax, same wording as glow-block)
- `templates/blocks/_stripes.html.twig` — partial in the style of `_glow.html.twig`:
  a single `aria-hidden` div, classes `stripes-block stripes-{{ variant }} stripes-color-{{ stripeColor }}`
  plus `parallax` when depth set; inline style built from the free-text fields via CSS
  custom properties (`--stripe-angle`, `--stripe-width`, `--stripe-opacity`, `--depth`)
  and position offsets.
- `assets/common/styles/components/stripes.scss` — `.stripes-block` renders the
  repeating-linear-gradient from the custom properties; per-color modifier classes map
  to tokens. Add token `$warning: #F2C81E !default;` to
  `assets/common/styles/base/variables.scss` (needed for hazard; place it with the
  fill/element tokens block).

Wiring:
- `<type ref="stripes-block"/>` in `config/templates/blocks/background-block.xml`
  background region (next to color/glow/line).
- Dispatch branch in `templates/blocks/background-block.html.twig` background loop
  (`{% elseif element.type == 'stripes-block' %}` including the partial — mirror how
  glow-block is dispatched there).
- `@use "../common/styles/components/stripes.scss";` in `assets/odsct/app.scss`
  (components group).

Commit: `ftr: blocks: add stripes background element`

## Task 2: seat-progress-block

Leaf content block: a row of `total` dots — first `won` dots in primary, dots
`won+1..target` in accent, the rest muted — followed by a small mono label.
Design reference: design file lines 62–68 (hero; total=21 won=2 target=10,
label "2 modré z 21 · zatím", 1rem round dots, .5rem gap).

Files to create:
- `config/templates/blocks/seat-progress-block.xml` — key `seat-progress-block`.
  Standard header trio (sulu-display-name/id/classes), then:
  `total` number colspan 2 (min 1 max 99, default 21), `won` number colspan 2 (min 0),
  `target` number colspan 2 (min 0), `label` text_line colspan 6
  (preview tag priority 256).
- `templates/blocks/seat-progress-block.html.twig` — wrapper div
  `seat-progress-block {{ data.classes }}` with `id`, a loop `1..total` emitting
  `<span class="dot is-won|is-target|is-rest">`, then `<span class="label">` when
  label set. Guard: if total empty/0 render nothing.
- `assets/common/styles/blocks/seat-progress-block.scss` — flex row, .5rem gap,
  1rem dots, border-radius 50%; colors `$primaryColor` (won), `$accentColor` (target),
  muted from `$border`/grey tokens; label small + letter-spaced.

Wiring (leaf content block — no content-loop container special casing needed beyond
the standard pattern):
- Dispatch branch in `templates/blocks/content-loop.html.twig` wrapped in
  `<div class="container">` (mirror the icon-block branch added recently).
- `<type ref="seat-progress-block"/>` in: `config/templates/pages/default.xml`,
  `config/templates/pages/article.xml` (next to icon-block),
  `config/templates/blockComponents/grid-block-types.xml` column content
  (next to icon-block), and `config/templates/blocks/background-block.xml`
  content region.
- Check how grid columns dispatch their children (`templates/blocks/grid-block.html.twig`)
  and add the branch there if dispatch is explicit rather than delegated to content-loop.
- `@use "../common/styles/blocks/seat-progress-block.scss";` in `assets/odsct/app.scss`.

Commit: `ftr: blocks: add seat progress block`

## Task 3: project-map-block

Section-level block: a large image (map) with absolutely-positioned numbered pins,
each pin a child block. Design reference: design file lines 228–236 — container
26.25rem tall, pins at percentage positions, each pin = colored 2.125rem circle with
number (or ★) + dark rounded label chip "Name · year".

Files to create:
- `config/templates/blocks/project-map-block.xml` — key `project-map-block`.
  Standard header trio, then: `image` single_media_selection colspan 6,
  `height` text_line colspan 3 (blank = 26.25rem), `link` link colspan 3, and a
  block region `projects` (default-type `project`, `block_id_generator` param like the
  other block regions, add_button_text "Add project") with a single inline type
  `project`: `number` text_line colspan 2, `label` text_line colspan 4 (preview tag),
  `year` text_line colspan 2, `tone` single_select colspan 2 (`default` | `highlight` —
  highlight = accent/star pin), `x` text_line colspan 1 (left, e.g. `22%`),
  `y` text_line colspan 1 (top, e.g. `16%`).
- `templates/blocks/project-map-block.html.twig` — wrapper with height style;
  if `data.image` render it as cover image, else render a `.map-placeholder` div
  (striped placeholder look — reuse the placeholder class introduced in Task 4 if
  already merged, otherwise a local striped background); loop `data.projects`:
  positioned pin wrapper (`left: x; top: y`), circle span + chip span
  (`label`~`· year`). Whole block wrapped in link when `link` set.
- `assets/common/styles/blocks/project-map-block.scss` — relative container,
  overflow hidden, shadow like other media blocks; pin circle (default `$brand`,
  highlight `$warning`), chip dark pill.

Wiring:
- content-loop dispatch branch wrapped in `<div class="container">`.
- `<type ref="project-map-block"/>` in pages `default.xml` + `article.xml`, and
  `config/templates/blocks/background-block.xml` content region (design places it
  inside a dark background section).
- `@use "../common/styles/blocks/project-map-block.scss";` in `assets/odsct/app.scss`.

Commit: `ftr: blocks: add project map block`

## Task 4: image-block striped placeholder

When an image-block has no image selected, render a striped placeholder instead of
nothing, so the page structure is visible before content/photos exist. Design shows
the look everywhere (e.g. lines 72, 191, 218, 245): diagonal repeating stripes in a
light neutral duo.

- `templates/blocks/image-block.html.twig`: read the current no-image behavior first.
  Add an `{% else %}` (or guard) branch: when no image is selected, emit
  `<div class="image-placeholder" …>` honoring the block's existing sizing fields the
  same way the real image path does (classes, maxWidth/maxHeight, id). Default aspect
  ratio 2:3 via CSS `aspect-ratio` when the block specifies no explicit size.
- `assets/common/styles/blocks/image-block.scss`: `.image-placeholder` —
  CSS `repeating-linear-gradient(45deg, …)` stripes using light token-derived neutrals
  (e.g. `$soft`/`$frost`), `aspect-ratio: 2 / 3`, max-width 100%. A CSS gradient gives
  the identical visual to a bundled SVG with no asset pipeline; if an actual SVG file
  proves necessary (it should not), place it under `assets/common/img/`.
- No XML changes. No new wiring — image-block is already wired everywhere.

Commit: `ftr: image-block: add striped placeholder when image is missing`

---

# Phase 1: theme pass (the odsct visual identity)

This is what makes the site LOOK like odsct.cz. Tasks 5–8. Colors/fonts live in the
`odsct` webspace layer; token *support* (new `!default` tokens, headings referencing a
display-font token) is webspace-agnostic and lands in `assets/common/`. Design tokens
(design file lines 20–26): `$primaryColor #04408f`, `$contrastToPrimaryColor #fff`,
`$accentColor #f28c1e`, `$contrastToAccentColor #3a2405`, `$backgroundSoftColor #f2f0e9`,
`$footerBackgroundColor #14181f`, `$contrastToFooterBackgroundColor #e8ecf2`,
`$background #fbfaf7`; display font "Archivo Black", body "Archivo", mono
"IBM Plex Mono", handwritten "Caveat".

## Task 5: odsct color tokens

Override the theme in the webspace layer only — `assets/odsct/styles/base/variables.scss`
currently just `@forward`s common with no overrides. Read it and
`assets/common/styles/base/variables.scss` first.
- Set the theme tokens above via `@forward "…common…/variables.scss" with (…)` or the
  established override mechanism this file uses (match how the bootstrap intends per-webspace
  overrides — inspect `assets/campaign`/`website` equivalents in the upstream if unclear;
  otherwise the `!default` override pattern).
- Remap the fill/element tokens that `color-block`/`glow-block`/`card-block`/`stripes-block`
  read — `$brand`, `$brandDeep`, `$navy`, `$navyDeep`, `$indigo`, `$soft`, `$frost`,
  `$border`, `$ink` — to the odsct palette (navy sections → `#14181f`/deep, brand blue →
  `#04408f`, soft → `#f2f0e9`, etc.) so dark sections and glows render in odsct colors.
- Setting `$accentColor` to `#f28c1e` resolves the Task 1 deferred minor (stripes/glow
  `accent` becomes orange). Confirm `$warning` (#F2C81E, Task 1) still reads correctly.
- Webspace-agnostic rule does NOT apply here — this file IS the webspace layer.
- Verify: `docker compose exec asset-builder npm run build` compiles; visually reason that
  primary/accent/soft/footer now resolve to the design hexes.
Commit: `conf: odsct: set brand color tokens`

## Task 6: odsct fonts

Design fonts: Archivo (body/`$defaultFont`), Archivo Black (`$displayFont`, headings),
IBM Plex Mono (`$monoFont`, labels/kickers), Caveat (handwritten accents).
- Add `$displayFont` and `$monoFont` as new `!default` tokens in
  `assets/common/styles/base/variables.scss` (default both to `$defaultFont` so other
  webspaces are unaffected — webspace-agnostic).
- In `assets/common/styles/base/typography.scss`, make headings (h1–h4 / the heading
  classes) use `$displayFont` and add a mono utility driven by `$monoFont`. Read the file
  first; only swap the font-family source to the token, don't restyle.
- In `assets/odsct/styles/base/variables.scss` set `$defaultFont: "Archivo"`,
  `$displayFont: "Archivo Black"`, `$monoFont: "IBM Plex Mono"` (plus a `$handwrittenFont:
  "Caveat"` token used by Task 8's `.handwritten`).
- Load the fonts the way roboto is loaded today: `@fontsource/*` imports in
  `assets/odsct/app.ts` + deps in `package.json` (packages: `@fontsource/archivo`,
  `@fontsource/archivo-black`, `@fontsource/ibm-plex-mono`, `@fontsource/caveat` — verify
  exact names; if a package is unavailable, fall back to a Google Fonts `@import` in the
  odsct app.scss, matching the design's own font source). Run
  `docker compose exec asset-builder npm install` after editing package.json.
- Verify: tsc/lint/build pass; the fontsource CSS resolves.
Commits (house format, split by kind): `deps: add odsct fonts (archivo, caveat, ibm-plex-mono)`
then `conf: odsct: set brand fonts` (+ any common typography.scss change as
`ftr: typography: add display/mono font tokens`).

## Task 7: block-option additions from the design

Small option additions to existing common blocks (webspace-agnostic).
- `button-block`: add `warning` to the `color` single_select (XML) + its style in
  `assets/common/styles/components/btn.scss` (design "Harmonogram stavby" button, #f2c81e →
  use `$warning`). Read button-block.xml + btn.scss first.
- `glow-block`: add `accent` and `warning` to the `glowColor` single_select (XML) + the
  matching color branches in `assets/common/styles/components/glow.scss` (orange/yellow
  glows in the díra/makat sections).
- `card-block` watermark number: add an optional `watermarkNumber` text_line field (XML) and
  render it as a `.card-watermark` element in `templates/blocks/card-block.html.twig` +
  style in `assets/common/styles/components/card.scss` (design lines 88/95/… oversized index
  behind card content). Read card-block.xml + card-block.html.twig + card.scss first; keep
  the field optional (renders nothing when empty).
Verify: tsc/lint/build. Commit(s) by kind, e.g. `ftr: blocks: add design color/watermark options`
(split button/glow/card if cleaner).

## Task 8: text utility classes + CKEditor mirror

Editorial utility classes the design applies to text-blocks, usable via the `classes` field
and (where they belong in rich text) as CKEditor styles.
- Add utility classes to `assets/common/styles/base/typography.scss` (or a new
  `assets/common/styles/base/utilities.scss` `@use`d from odsct app.scss):
  `.chip-primary`, `.chip-accent` (mono kicker pills), `.handwritten` (uses
  `$handwrittenFont`), a mono label helper. Colors via tokens, webspace-agnostic.
- **Mirror** the visible ones into `assets/admin/style.css` per the CKEditor mirror rule so
  editors see them in the admin preview. Read the sulu-3 skill's ckeditor reference first.
- Optionally expose `.handwritten` / mono as CKEditor pen or paragraph styles in
  `assets/admin/app.js` (only if it reads cleanly — otherwise leave as class-only and note
  it). If admin/app.js is changed, run the admin Webpack build per
  `~/.claude/skills/sulu-3/references/admin-build.md` and hard-reload /admin.
- Verify: frontend tsc/lint/build; if admin changed, the admin build succeeds.
Commit: `ftr: typography: add odsct text utility classes` (+ `conf: admin: mirror utility classes`
if admin touched).

## Execution order (per user: theme before any content-mock)

Task 3 is already in flight — let it finish and review. Then run **5 → 6 → 7 → 8**
(the theme pass), then **4** (image placeholder, independent/trivial), then the final
whole-branch review. The content-mock against odsct.local is explicitly deferred until all
of the above is merged.
