#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/.."

# Ubuntu's Electron binary aborts unless chrome-sandbox is setuid root.
# Development does not have that ownership, so disable the sandbox here only.
if [[ "$(uname -s)" == "Linux" ]]; then
    export NO_SANDBOX=1
fi

exec npx concurrently -k -c "#93c5fd,#c4b5fd" \
    "php artisan native:serve --no-interaction" \
    "npm run dev" \
    --names=app,vite
