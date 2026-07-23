# ODSCT

> **Lineage:** based on the `sulu-init-3` bootstrap (`sulu-init` family). This is a standalone project copy — backport shared, project-agnostic improvements upstream, not project-specific ones.

**Stack:** Sulu ~3.0, Symfony ^7.4, PHP ^8.2, Vue 3 + Vite + TypeScript, SCSS, API Platform, PostgreSQL 15.

---

## Local development

### Requirements

- Docker + Docker Compose

No local PHP or Node required — everything runs in containers.

### Starting up

```bash
docker compose up -d
```

On first start the `web` container runs `composer install`, database migrations, and `php-fpm`. The `asset-builder` container runs `npm install` and starts the Vite dev server automatically.

Add the entries from `hosts.example` to `/etc/hosts` first.

| URL | Service |
|---|---|
| `http://odsct.local` | Website frontend |
| `http://odsct.local/admin` | Sulu admin |
| `http://adminer.odsct.local` | Adminer (database UI) |
| `http://mailer.odsct.local` | Mailpit (local mail catcher) |
| `http://vite.odsct.local` | Vite HMR dev server |

### Initialize Sulu

Creates default pages, roles, and settings (first-time setup only):

```bash
make sulu-init
```

Then open `http://odsct.local/admin`, log in with the default credentials, and create the required `websiteConfig` snippet (see `docs/`).

### Useful commands

```bash
# Run Symfony console commands
docker compose exec web bin/console <command>

# Install a Composer package
docker compose exec web composer require <package>

# Install an npm package (odsct assets)
docker compose exec asset-builder npm install <package>

# Production asset build
make build

# TypeScript + lint check
make check

# Re-initialize Sulu (wipes and rebuilds default content)
make sulu-init
```

### Sulu Admin JS build

The admin frontend uses a separate Webpack build, run in a temporary Node container (not the `asset-builder`). See [docs/sulu-admin-build.md](docs/sulu-admin-build.md) for the full workflow.

---

## Project structure

```
assets/
  common/           # Shared JS, SCSS, Vue
    js/
      blocks/       # JS behavior for individual blocks
      layout/       # Header, footer, carousel, etc.
      utils/        # Exported utility functions
    styles/
      blocks/       # Block-scoped styles
      components/   # Reusable UI components
      layout/       # Layout-level styles
      base/         # Variables, resets, typography
      fonts/        # Font definitions
    vue/            # Vue apps, plugins, stores
  odsct/            # odsct webspace entry points (app.ts, app.scss)
  admin/            # Sulu admin JS (Webpack, separate build)

config/
  webspaces/        # Webspace definitions (odsct.xml)
  templates/
    blocks/         # Block field definitions (.xml)
    snippets/       # Snippet field definitions (.xml)
    pages/          # Page template field definitions (.xml)

templates/
  blocks/           # Block Twig templates
  layout/           # Layout partials (header, footer, etc.)
  webspace/         # Root templates per webspace (odsct.html.twig)

src/
  ApiResource/      # API Platform resources
  Controller/       # Symfony controllers
  DTO/              # Data Transfer Objects
  Entity/           # Doctrine entities
  Service/          # Application services
  Sulu/Admin/       # Sulu admin customizations
```

### Webspace

Single webspace: **`odsct`**.

| | odsct |
|---|---|
| JS entry | `assets/odsct/app.ts` |
| SCSS entry | `assets/odsct/app.scss` |
| Variables | `assets/odsct/styles/base/variables.scss` |
| Root template | `templates/webspace/odsct.html.twig` |

All shared code lives in `assets/common/` and is wired into the `odsct` entry point.

### Block anatomy

Every block has 4 parts that must be kept consistent:

1. `config/templates/blocks/<name>.xml` — Sulu field definitions
2. `templates/blocks/<name>.html.twig` — HTML structure and CSS class names
3. `assets/common/styles/blocks/<name>.scss` — styles, keyed to Twig classes
4. `assets/common/js/blocks/<name>.ts` — JS behavior, selectors must match the Twig

The Twig template is the source of truth for class names and DOM structure.
