<p align="center">
  <a href="https://roots.io/acorn/">
    <img alt="Acorn" src="https://cdn.roots.io/app/uploads/logo-acorn.svg" height="100">
  </a>
</p>

<p align="center">
  <a href="LICENSE.md">
    <img alt="MIT License" src="https://img.shields.io/github/license/roots/acorn?color=%23525ddc&style=flat-square" />
  </a>
  
  <a href="https://laravel.com/docs/9.x">
    <img alt="Laravel v9" src="https://img.shields.io/static/v1?label=laravel&message=v9&logo=Laravel&style=flat-square&color=f9322c" />
  </a>

  <a href="https://packagist.org/packages/roots/acorn">
    <img alt="Release" src="https://img.shields.io/github/release/roots/acorn.svg?style=flat-square" />
  </a>

  <a href="https://github.com/roots/acorn/actions">
    <img alt="Build Status" src="https://img.shields.io/github/actions/workflow/status/roots/acorn/main.yml?branch=main&style=flat-square" />
  </a>

  <a href="https://twitter.com/rootswp">
    <img alt="Follow Roots" src="https://img.shields.io/twitter/follow/rootswp.svg?style=flat-square&color=1da1f2" />
  </a>
</p>

<p align="center">
  <strong>Laravel components for WordPress plugins and themes</strong>
</p>

<p align="center">
  <a href="https://roots.io/"><strong><code>Website</code></strong></a> &nbsp;&nbsp; <a href="https://roots.io/acorn/docs/installation/"><strong><code>Documentation</code></strong></a> &nbsp;&nbsp; <a href="https://github.com/roots/acorn/releases"><strong><code>Releases</code></strong></a> &nbsp;&nbsp; <a href="https://discourse.roots.io/"><strong><code>Support</code></strong></a>
</p>

## Sponsors

**Acorn** is an open source project and completely free to use.

However, the amount of effort needed to maintain and develop new features and products within the Roots ecosystem is not sustainable without proper financial backing. If you have the capability, please consider [sponsoring Roots](https://github.com/sponsors/roots).

<p align="center"><a href="https://github.com/sponsors/roots"><img height="32" src="https://img.shields.io/badge/sponsor%20roots-525ddc?logo=github&logoColor=ffffff&message=" alt="Sponsor Roots"></a></p>

<div align="center">
<a href="https://k-m.com/"><img src="https://cdn.roots.io/app/uploads/km-digital.svg" alt="KM Digital" width="148" height="111"></a> <a href="https://carrot.com/"><img src="https://cdn.roots.io/app/uploads/carrot.svg" alt="Carrot" width="148" height="111"></a> <a href="https://wordpress.com/"><img src="https://cdn.roots.io/app/uploads/wordpress.svg" alt="WordPress.com" width="148" height="111"></a> <a href="https://pantheon.io/"><img src="https://cdn.roots.io/app/uploads/pantheon.svg" alt="Pantheon" width="148" height="111"></a> <a href="https://worksitesafety.ca/careers/"><img src="https://cdn.roots.io/app/uploads/worksite-safety.svg" alt="Worksite Safety" width="148" height="111"></a>
</div>

## Overview

Acorn is a way to use Laravel components inside of WordPress.

![Acorn CLI output](https://cdn.roots.io/app/uploads/wp-cli-acorn-v2.png)

## Requirements

See the full [installation](https://roots.io/acorn/docs/installation/) docs for requirements.

## Installation

Navigate to your Bedrock directory (recommended), or your Sage-bsaed theme directory, and run the following command:

```sh
$ composer require roots/acorn
```

To install the latest development version of Acorn, add `dev-main` to the end of the command:

```sh
$ composer require roots/acorn dev-main
```

We recommend adding Acorn's `postAutoloadDump` function to Composer's `post-autoload-dump` event in `composer.json`:

```json
"post-autoload-dump": [
  "Roots\\Acorn\\ComposerScripts::postAutoloadDump"
]
```

## Community

Keep track of development and community news.

- Join us on Discord by [sponsoring us on GitHub](https://github.com/sponsors/roots)
- Participate on the [Roots Discourse](https://discourse.roots.io/)
- Follow [@rootswp on Twitter](https://twitter.com/rootswp)
- Read and subscribe to the [Roots Blog](https://roots.io/blog/)
- Subscribe to the [Roots Newsletter](https://roots.io/newsletter/)
