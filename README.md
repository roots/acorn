<p align="center">
  <a href="https://roots.io/acorn/">
    <img alt="Acorn" src="https://cdn.roots.io/app/uploads/logo-acorn.svg" height="100">
  </a>
</p>

<p align="center">
  <a href="LICENSE.md">
    <img alt="MIT License" src="https://img.shields.io/github/license/roots/acorn?color=%23525ddc&style=flat-square" />
  </a>
  
  <a href="https://laravel.com/docs/8.x">
    <img alt="Laravel v8" src="https://img.shields.io/static/v1?label=laravel&message=v8&logo=Laravel&style=flat-square&color=f9322c" />
  </a>

  <a href="https://github.com/roots/trellis/acorn">
    <img alt="Release" src="https://img.shields.io/github/release/roots/acorn.svg?style=flat-square" />
  </a>

  <a href="https://github.com/roots/acorn/actions">
    <img alt="Build Status" src="https://img.shields.io/github/workflow/status/roots/acorn/Main?style=flat-square" />
  </a>

  <a href="https://twitter.com/rootswp">
    <img alt="Follow Roots" src="https://img.shields.io/twitter/follow/rootswp.svg?style=flat-square&color=1da1f2" />
  </a>
</p>

<p align="center">
  <strong>Laravel components for WordPress plugins and themes</strong>
</p>

<p align="center">
  <a href="https://roots.io/"><strong><code>Website</code></strong></a> &nbsp;&nbsp; <a href="https://docs.roots.io/acorn/2.x/installation/"><strong><code>Documentation</code></strong></a> &nbsp;&nbsp; <a href="https://github.com/roots/acorn/releases"><strong><code>Releases</code></strong></a> &nbsp;&nbsp; <a href="https://discourse.roots.io/"><strong><code>Support</code></strong></a>
</p>

## Supporting

**Acorn** is an open source project and completely free to use.

However, the amount of effort needed to maintain and develop new features and products within the Roots ecosystem is not sustainable without proper financial backing. If you have the capability, please consider donating using the links below:

<div align="center">

[![Sponsor on GitHub](https://img.shields.io/static/v1?label=sponsor&message=%E2%9D%A4&logo=GitHub&style=flat-square)](https://github.com/sponsors/roots)
[![Sponsor on Patreon](https://img.shields.io/badge/sponsor-patreon-orange.svg?style=flat-square&logo=patreon")](https://www.patreon.com/rootsdev)
[![Donate via PayPal](https://img.shields.io/badge/donate-paypal-blue.svg?style=flat-square&logo=paypal)](https://www.paypal.me/rootsdev)

</div>

## Overview

Acorn is a way to use Laravel components inside of WordPress.

## Requirements

See the full [installation](https://docs.roots.io/acorn/2.x/installation/) docs for requirements.

## Installation

To install Acorn in a Bedrock environment, navigate to your Bedrock directory and run the following command:

```sh
$ composer require roots/acorn
```

We recommend adding Acorn's `postAutoloadDump` function to Composer's `post-autoload-dump` event in `composer.json`:

```json
"post-autoload-dump": [
  "Roots\\Acorn\\ComposerScripts::postAutoloadDump"
]
```

## Sponsors

Help support our open-source development efforts by [becoming a GitHub sponsor](https://github.com/sponsors/roots) or [patron](https://www.patreon.com/rootsdev).

<a href="https://k-m.com/"><img src="https://cdn.roots.io/app/uploads/km-digital.svg" alt="KM Digital" width="200" height="150"></a> <a href="https://carrot.com/"><img src="https://cdn.roots.io/app/uploads/carrot.svg" alt="Carrot" width="200" height="150"></a> <a href="https://www.c21redwood.com/"><img src="https://cdn.roots.io/app/uploads/c21redwood.svg" alt="C21 Redwood Realty" width="200" height="150"></a> <a href="https://wordpress.com/"><img src="https://cdn.roots.io/app/uploads/wordpress.svg" alt="WordPress.com" width="200" height="150"></a> <a href="https://pantheon.io/"><img src="https://cdn.roots.io/app/uploads/pantheon.svg" alt="Pantheon" width="200" height="150"></a>

## Community

Keep track of development and community news.

- Join us on Roots Slack by becoming a [GitHub sponsor](https://github.com/sponsors/roots) or [patron](https://www.patreon.com/rootsdev)
- Participate on the [Roots Discourse](https://discourse.roots.io/)
- Follow [@rootswp on Twitter](https://twitter.com/rootswp)
- Read and subscribe to the [Roots Blog](https://roots.io/blog/)
- Subscribe to the [Roots Newsletter](https://roots.io/subscribe/)
