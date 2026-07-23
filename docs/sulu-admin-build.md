# Sulu Admin Build

The project has two independent build systems:

| | Website / Campaign | Sulu Admin |
|---|---|---|
| Entry | `package.json` (root) | `assets/admin/package.json` |
| Bundler | Vite | Webpack |
| Container | `asset-builder` (auto on startup) | temporary `node:24.11.1` container |
| Output | `public/build/` | `public/admin/` |

The `asset-builder` container automatically runs `npm install && npm run dev` (Vite) on startup. The Sulu admin is built in a separate temporary Node container as recommended by Sulu docs.

---

## Entering the build container

Run from the project root:

```bash
docker run --rm --interactive --tty --volume ${PWD}:/var/project node:24.11.1 /bin/bash
```

All commands below are run **inside this container**.

---

## Routine — after changing admin JS

```bash
cd /var/project/assets/admin
npm run build
```

## After adding an npm dependency to `assets/admin/package.json`

```bash
cd /var/project/assets/admin
npm install
npm run build
```

## Clean reinstall — when node_modules are stale or broken

```bash
rm -rf /var/project/assets/admin/node_modules /var/project/assets/admin/package-lock.json
rm -rf /var/project/vendor/sulu/sulu/node_modules /var/project/vendor/sulu/sulu/package-lock.json
rm -rf /var/project/vendor/sulu/sulu/src/Sulu/Bundle/*/Resources/js/node_modules
rm -rf /var/project/vendor/sulu/sulu/src/Sulu/Bundle/*/Resources/js/package-lock.json
cd /var/project/assets/admin
npm install
npm run build
```

## Watch mode — during active admin JS development

```bash
cd /var/project/assets/admin
npm install
npm run watch
```

---

## Notes

- `npm install` triggers Sulu's `preinstall` / `postinstall` hooks automatically — no separate vendor installs needed.
- After adding a new bundle (e.g. `sulu-ai-translator-bundle`), add the `file:` dependency to `assets/admin/package.json` first, then run the "after adding" sequence above.
- The admin build output lands in `public/admin/`. No PHP cache clear is needed after a JS-only change.

## CKEditor version pinning

CKEditor 47.7+ has a bug (`license-key-lts-not-allowed`) that breaks the editor entirely in Sulu custom builds (see [sulu/sulu#8811](https://github.com/sulu/sulu/issues/8811)). All `@ckeditor/*` runtime packages are pinned to `47.6.2` via the `overrides` section in `package.json`. When adding any new CKEditor package, pin it to `47.6.2` in both `devDependencies` and `overrides`.
