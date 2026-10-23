#!/usr/bin/env bash
set -euo pipefail

root="$(cd "$(dirname "$0")/.." && pwd)"
payload="$root/snap/payload"
installed="/opt/Phost"

if [[ ! -x "$installed/phost" || ! -x "$installed/phost.bin" ]]; then
    echo "Install the .deb first, or build it with: composer native:linux" >&2
    exit 1
fi

rm -rf "$payload"
mkdir -p "$payload"
cp -a "$installed"/. "$payload"/

if ! command -v lxc >/dev/null 2>&1; then
    echo "Falta LXD. En esta máquina Snapcraft no puede empaquetar directo porque Ubuntu Pro está activo." >&2
    echo "sudo snap install lxd" >&2
    echo "sudo lxd init --auto" >&2
    exit 1
fi

cd "$root"
snapcraft pack --use-lxd
echo "Snap written in $root"
