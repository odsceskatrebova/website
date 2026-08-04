#!/usr/bin/env bash
# Server-side deploy for odsct.cz — run from the project root: ./deploy.sh
set -euo pipefail

# Everything lives inside main() so a git pull rewriting this file
# mid-run cannot corrupt the executing script.
main() {
    cd "$(dirname "$0")"

    git pull --ff-only

    # nvm is a shell function; nvm.sh is not set -u clean
    export NVM_DIR="$HOME/.nvm"
    set +u
    . "$NVM_DIR/nvm.sh"
    nvm use 20
    set -u

    npm install
    npm run build

    # Caches are owned by www-data, hence sudo. var/share holds the HTTP
    # response cache (SuluHttpCache store) — stale entries there keep serving
    # HTML that references deleted hashed assets. php-fpm rebuilds both dirs
    # with correct ownership on the first request, so no warmup as this user.
    sudo rm -rf var/cache/* var/share/*

    echo "--- verify ---"
    curl -s -o /dev/null -w "homepage: %{http_code}\n" https://www.odsct.cz/
    echo "assets referenced by the live page:"
    curl -s https://www.odsct.cz/ | grep -o 'odsct-[A-Za-z0-9_-]*\.\(css\|js\)' | sort -u
    echo "assets present on disk:"
    ls public/build/odsct/assets/ | grep '^odsct-'
}

main "$@"
exit 0
