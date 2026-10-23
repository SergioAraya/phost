<p align="center">
<img src="https://github.com/gwleuverink/phost/blob/main/storage/app/public/icon.png?raw=true" alt="Logo" width="260" />
</p>

<p align="center">
<a href="https://github.com/gwleuverink/phost/actions/workflows/test.yml"><img src="https://github.com/gwleuverink/phost/actions/workflows/test.yml/badge.svg" alt="Tests"></a>
<a href="https://github.com/gwleuverink/phost/actions/workflows/codestyle.yml"><img src="https://github.com/gwleuverink/phost/actions/workflows/codestyle.yml/badge.svg" alt="Codestyle"></a>
</p>


## About

Phost is a email debugging tool and local SMTP server, developed primarily in PHP. 
Instead of sending your development emails to Mailtrap or MailHog, you can use Phost to debug your emails inside a beautiful self-contained desktop app.

We leverage a powerful stack of modern technologies:

-   [**Laravel/Livewire**](https://livewire.laravel.com/) -> For dynamic, reactive interface
-   [**NativePHP**](https://nativephp.com/) -> Electron wrapper for PHP built apps
-   [**ReactPHP**](https://reactphp.org/) -> Powering the SMTP server component

While Phost may not yet match the feature set (or stability) of some paid alternatives, it serves as an exploration of the capabilities of a native PHP stack.

<img src="https://github.com/gwleuverink/phost/blob/main/storage/app/public/screenshots/filled-inbox.png?raw=true" alt="Filled inbox screenshot" />
<img src="https://github.com/gwleuverink/phost/blob/main/storage/app/public/screenshots/settings.png?raw=true" alt="User preferences screenshot" />
<!-- <img src="https://github.com/gwleuverink/phost/blob/main/storage/app/public/screenshots/inbox-zero.png?raw=true" alt="Inbox zero! screenshot" /> -->

## Features

-   Local SMTP server for intercepting and viewing emails
-   User-friendly interface for email inspection
-   Debug email source, both HTML, text & headers
-   Bookmark emails for quick access
-   Light & dark color schemes
-   Print emails as PDF
-   Spanish and English interface, with Spanish as the default
-   Separate mailboxes per project, each with its own SMTP port and connection settings

## Getting started

macOS builds are on the [releases page](https://github.com/gwleuverink/phost/releases).

### Ubuntu

The `.deb` needs these packages. On Ubuntu 24.04 and newer, GTK and AT-SPI use the `t64` names. Ubuntu 22.04 still ships `libgtk-3-0` and `libatspi2.0-0`.

```bash
sudo apt install libgtk-3-0t64 libnotify4 libnss3 libxss1 libxtst6 xdg-utils libatspi2.0-0t64 libuuid1 libsecret-1-0
sudo dpkg -i phost_*_amd64.deb
```

`libxss1` is the one a desktop install often lacks. `libappindicator3-1` is optional and only adds the status icon. If `dpkg` stops because a package is missing, finish with `sudo apt install -f`.

## Supporting the Project

To distribute Phost on macOS, the application needs to be signed and notarized through the Apple Developer Program, which costs €100 annually. If you find Phost useful, please consider supporting its development and maintenance through [GitHub Sponsors](https://github.com/sponsors/gwleuverink). Your support helps cover these costs and enables continued development of this open-source tool.
