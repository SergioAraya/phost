#!/usr/bin/env bash
set -euo pipefail

root="$(cd "$(dirname "$0")/.." && pwd)"
deb="$(find "$root/dist" -maxdepth 1 -name '*.deb' -print -quit)"

if [[ -z "$deb" ]]; then
    echo "No .deb found in dist/" >&2
    exit 1
fi

stage="$(mktemp -d)"
trap 'rm -rf "$stage"' EXIT

dpkg-deb -x "$deb" "$stage"
dpkg-deb -e "$deb" "$stage/DEBIAN"

postinst="$stage/DEBIAN/postinst"
if [[ -f "$postinst" ]] && ! grep -q 'gtk-update-icon-cache' "$postinst"; then
    cat >> "$postinst" << 'EOF'

if command -v gtk-update-icon-cache >/dev/null; then
    gtk-update-icon-cache -f -t /usr/share/icons/hicolor || true
fi
EOF
    chmod 755 "$postinst"
fi

binary="$stage/opt/Phost/phost"
if [[ ! -f "$binary" ]]; then
    echo "Packaged executable not found at $binary" >&2
    exit 1
fi

desktop="$stage/usr/share/applications/phost.desktop"
if [[ -f "$desktop" ]]; then
    sed -i '/^MimeType=x-scheme-handler\/;$/d' "$desktop"
fi

icon_src="$stage/usr/share/icons/hicolor/2048x2048/apps/phost.png"
if [[ -f "$icon_src" ]] && command -v magick >/dev/null; then
    for size in 48 64 128 256 512; do
        dest_dir="$stage/usr/share/icons/hicolor/${size}x${size}/apps"
        mkdir -p "$dest_dir"
        magick "$icon_src" -resize "${size}x${size}" "$dest_dir/phost.png"
    done
fi

if [[ "$(head -c 2 "$binary")" != "#!" ]]; then
    mv "$binary" "$stage/opt/Phost/phost.bin"
    cat > "$binary" << 'EOF'
#!/bin/sh
# Ubuntu refuses the unpacked chrome-sandbox helper unless it is setuid root.
# The package cannot set that bit, so start Electron without the sandbox.
exec "$(dirname "$0")/phost.bin" --no-sandbox "$@"
EOF
    chmod 755 "$binary"
fi

(
    cd "$stage"
    find . -type f ! -path './DEBIAN/*' -print0 | sort -z | xargs -0 md5sum > DEBIAN/md5sums
)

dpkg-deb -b "$stage" "$deb" >/dev/null
echo "Wrapped $deb"
