# Spec: odsct header/footer config wiring

Date: 2026-08-01
Status: approved design, pending implementation plan

## Context

The odsct campaign design is mocked up in `templates/webspace/odsct/header.html.twig`
and `templates/webspace/odsct/footer.html.twig` — every string is hardcoded. This work
wires header and footer to editable sources: the `websiteConfig` snippet (already
loaded as `config`/`configView` in `webspace/odsct.html.twig`) and the `header`
navigation context. It also closes publish gaps found during exploration: missing
footer legal row, no mobile menu (header nav is `display: none` under 34rem), generic
unstyled error page, and unstyled cookie bar/settings.

Content currently on odsct.local is test-only; production content is entered
separately. This spec changes structure, templates, and styling — not content.

## Decisions (from brainstorming)

- Footer quick links ("RYCHLÉ ODKAZY") and elsewhere links ("JINDE") live **outside**
  Sulu's menu system, as two fixed blocks in the config snippet. Link labels support
  a highlighted part (mock: `odsct.cz<span>/program</span>`).
- Social networks are plain entries in the JINDE block — no icon row, no special
  social handling. The `footerSocialIcons` svg-icon block is removed.
- Election date is stored as **real date fields** (start + end); a Twig extension
  formats the Czech range. Enables future date logic (e.g. countdown).
- Contacts are structured fields (`contactEmail`, `contactPhone`), not rich text.
- Value points: block with **unlimited** count; the grid wraps beyond 6.
- Unused bootstrap fields are **pruned** from `websiteConfig.xml`. This repo is
  standalone; bootstrap compatibility is not a goal. The generic `templates/layout/`
  header/footer keep referencing removed fields but are never rendered (only the
  odsct webspace exists, and `base.html.twig` maps every page to
  `webspace/odsct.html.twig`).
- Mobile menu overlay contains nav + CTA only.
- In scope additionally: footer legal row, mobile menu, error page styling, cookie
  bar + cookie settings restyle with the reopen trigger in the footer.
- Footer group labels (RYCHLÉ ODKAZY / JINDE / KONTAKT) stay hardcoded in the
  template — single-locale (cs) site, two fixed link blocks.

## 1. `websiteConfig.xml` restructure

Remove: `logo`, header `mainContact`, footer `footerSocialIcons`, footer
`footerContent`. Keep `title` (node name, mandatory).

New/kept properties:

**Section `brand`** (shared by header + footer):

| name | type | notes |
|---|---|---|
| `badgeText` | text_line, mandatory | "ODS 2026" |
| `electionLabel` | text_line | "KOMUNÁLNÍ VOLBY" |
| `electionDateStart` | date | first election day |
| `electionDateEnd` | date | second election day; optional (single-day fallback) |
| `cityText` | text_line | "ČESKÁ TŘEBOVÁ" |

**Section `header`** (kept from current XML):

| name | type | notes |
|---|---|---|
| `ctaText` | text_line, mandatory | "Přijďte volit" |
| `ctaLink` | single_page_selection | existing params kept |

**Section `footer`:**

| name | type | notes |
|---|---|---|
| `valuePoints` | block, type `point` | unlimited count |
| `valuePoints/pointTitle` | text_line | "Držíme slovo." |
| `valuePoints/pointText` | text_area | one-two sentences |
| `brandDescription` | text_area | rendered with `nl2br` |
| `footerQuickLinks` | block, type `link` | RYCHLÉ ODKAZY list |
| `footerElsewhereLinks` | block, type `link` | JINDE list, incl. socials |
| `contactEmail` | text_line | rendered as `mailto:` |
| `contactPhone` | text_line | rendered as `tel:` (spaces stripped in href) |
| `footerQuote` | text_line | handwritten line, "Uvidíme se na náměstí." |
| `footerPicture` | single_media_selection (image) | optional; section omitted when unset |
| `copyrightText` | text_line | kept; `%Y%` replaced with current year |
| `gdprLink` | link | kept; rendered in legal row |

**Block type `link`** (used by both link blocks):

| name | type | notes |
|---|---|---|
| `text` | text_line, mandatory | plain part, e.g. "odsct.cz" |
| `highlightText` | text_line | optional accent `<span>` part, e.g. "/program" |
| `link` | link (internal + external) | target |

Both blocks use `add_button_text` + `block_id_generator` params and
`sulu.block_preview` on `text`, following existing snippet conventions.
Field titles follow house style (lower-case), with cs + en meta titles.

## 2. Webspace navigation contexts

`config/webspaces/odsct.xml`: trim `<navigation>` contexts to just `header`.
`footer1`, `footer2`, `footer-legal` are removed — footer links come from config
blocks, the legal row from config fields.

## 3. Header template + mobile menu

`templates/webspace/odsct/header.html.twig`:

- Brand link: `badgeText` badge; tagline composed as
  `electionLabel<br>{date range} · cityText` (parts omitted when empty).
- Nav: `sulu_page_navigation_root_flat('header', 1, { title, uuid })` rendered as
  `<sulu-link>` items, replacing hardcoded anchors. The CTA (`ctaText`/`ctaLink`)
  renders with the existing `site-header__cta` styling; nav falls back to nothing
  (no error) when the context is empty.
- Mobile: hamburger toggle button appears under the existing 34rem breakpoint
  (replacing today's `display: none` on the nav); it opens a full-viewport
  campaign-styled overlay containing the same nav links + CTA. Small TS toggle —
  reuse the bootstrap menu-toggle pattern from `assets/common` if it fits the
  markup, otherwise a small dedicated script imported from `assets/odsct/app.ts`.
  Overlay closes on link click, close button, and Escape.

## 4. Footer template + legal row

`templates/webspace/odsct/footer.html.twig`:

- Principles grid: loop `config.valuePoints` (title + text). Grid wraps beyond 6.
- Brand column: `badgeText`, `electionLabel · {date range}`, `brandDescription|nl2br`.
- Links column: hardcoded "RYCHLÉ ODKAZY" label + `footerQuickLinks` loop, then
  hardcoded "JINDE" label + `footerElsewhereLinks` loop. Each entry renders
  `text` plus optional `<span>{highlightText}</span>`; href from the `link` value.
- Contact column: "KONTAKT" label, `mailto:`/`tel:` lines, `footerQuote` in the
  handwritten style. Lines render only when the field is filled.
- Photo section: renders `footerPicture` via the responsive image markup pattern
  used by `image-block` (proper formats/srcset), inside the existing frame styling.
  Entire section omitted when no media selected.
- **New legal row** (slim bottom band, campaign-styled): copyright with `%Y%`
  year replacement, `gdprLink`, and a `data-cookie-settings-trigger` link labeled
  `cookieSettings.openLink|trans` to reopen cookie settings.

## 5. Twig date-range extension

New Twig extension in `src/Sulu/` (following existing extension registration
conventions in this repo) exposing a function `election_date_range(start, end)`
that formats two dates Czech-style:

- same month + year → `25.–26. 9. 2026`
- different month → `30. 9. – 1. 10. 2026`
- end missing or equal to start → `26. 9. 2026`

Unit-testable pure formatting; used by both header and footer.

## 6. Cookie bar + cookie settings restyle

The Vue apps (`assets/common/vue/apps/CookieBar.vue`, `CookieSettings.vue`) are
mounted in `webspace/odsct.html.twig` and cs translations are complete (both
`assets/common/vue/translations/cs/` and `translations/messages.cs.yaml`). Work:

- Restyle bar + settings modal to the campaign theme via odsct-layer SCSS
  (overrides/tokens in `assets/odsct/styles/`, keeping `assets/common` webspace-
  agnostic per house rules).
- Footer legal row hosts the reopen trigger (section 4).
- Verify the consent flow end-to-end on odsct.local (accept / reject / reopen
  via footer trigger / save preferences).

## 7. Error page

`templates/error/error.html.twig` (receives odsct chrome via `base.html.twig`):
campaign-styled status code in the badge style, short cs message, CTA back to
homepage. New `assets/odsct/styles/layout/error.scss`, `@use`d from
`assets/odsct/app.scss`. A missing `websiteConfig` snippet must not break the
error page (the base template already guards with the error-box branch).

## 8. Assets

- SCSS: extend `assets/odsct/styles/layout/header.scss` (mobile menu, toggle) and
  `footer.scss` (photo rendering, legal row); new `error.scss`; cookie restyle
  styles. Colors via existing tokens; sizing in rem; house conventions apply.
- TS: mobile menu toggle (see section 3). No other JS.
- Admin build is untouched (no new content types); XML changes need website
  cache clear only. npm/composer run via their containers per project rules.

## 9. Favicon

Download the ODS bird logo from the official ODS website and generate a full
favicon set: `favicon.ico` (multi-size), PNG sizes, `apple-touch-icon.png`,
placed in `public/` (ico at the webroot) and linked from the odsct head.

## Out of scope (publish checklist, tracked separately)

- Search page (`search/search.html.twig`) is generic English — decide whether
  search is reachable at all; style or remove the route before publish.
- Welcome popup content check (snippet area exists and is wired).
- Verify OG/SEO defaults (OG image path already fixed).
- Analytics configuration in admin (Sulu analytics templates present).
- robots.txt / sitemap sanity check on prod domain.
- Production content entry (handled by the user directly on prod).

## Verification

- `docker compose exec asset-builder npm run tsc` and `npm run lint`; `npm run build`
  for SCSS compile.
- Manual pass on odsct.local: header nav from admin menu area, config edits reflected
  in header/footer, empty-field fallbacks (no photo, no quote, missing dates), mobile
  menu at <34rem, cookie flow, error page (404), legal row rendering.
- PHP: unit test for the date-range formatter.
