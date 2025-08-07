#!/bin/sh

set -eux

# Install volta
curl https://get.volta.sh | bash -s -- --version latest
export VOLTA_HOME="${HOME}/.volta"
export PATH="${VOLTA_HOME}/bin:${PATH}"
volta install node
volta install bun

WORKSPACE_FOLDER="${WORKSPACE_FOLDER:-"${PWD##*/}"}"

# WordPress site title - defaults to "Acorn Testing", can be overridden
WP_SITE_TITLE="${WP_SITE_TITLE:-"Acorn Testing"}"

# source our application env vars to be used here
. '/roots/app/.env';

# We need a fallback URL if WP_HOME isn't set
WP_HOME="${WP_HOME:-'http://localhost:8080'}"
[ -z ${CODESPACE_NAME+x} ] || WP_HOME="https://${CODESPACE_NAME}-8080.${GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN}"

# Show dirty git prompt
cd /roots/acorn
git config devcontainers-theme.show-dirty 1

# Install WordPress and activate the plugin/theme.
cd /roots/app

if [ -f 'package.json' ]; then
    bun install || true
    bun run build || true
fi

wp db reset --yes
wp core install --url="${WP_HOME}" --title="${WP_SITE_TITLE}" --admin_user="admin" --admin_email="admin@roots.test" --admin_password="password" --skip-email

# Add sage if it's not active
if ! wp theme status sage --skip-plugins --skip-themes 2>/dev/null | grep -q "^sage.*active"; then
    # Only require sage if it doesn't exist
    if ! wp theme is-installed sage --skip-plugins --skip-themes 2>/dev/null; then
        # Remove existing Acorn to replace with version-aliased one
        composer remove roots/acorn --no-update
        # Add our local Acorn repository with version alias
        composer config repositories.acorn '{"type": "path", "url": "/roots/acorn", "options": {"versions": {"roots/acorn": "5.0.x-dev"}}}'
        # Install Sage v11 with our aliased Acorn
        composer require "roots/sage:^11.0" "roots/acorn:5.0.x-dev" -W
    fi
    wp theme activate sage
    # Build the Sage theme
    cd $(wp theme path --skip-plugins --skip-themes 2>/dev/null)/sage
    # Remove Sage's vendored Acorn to use Bedrock's version
    composer remove roots/acorn --no-update
    composer update --no-install
    bun install && bun run build
fi

install_theme() {
    cd $1
    pwd
    if [ -f 'composer.json' ]; then
        composer install || true
    fi
    if [ -f 'package.json' ]; then
        bun install || true
        bun run build || true
    fi
}

find $(wp theme path --skip-plugins --skip-themes 2>/dev/null) -mindepth 1 -maxdepth 1 -type d | \
    while read theme; do install_theme "$theme"; done

wp dotenv salts regenerate --skip-plugins --skip-themes 2>/dev/null || true
wp rewrite structure '%postname%' --hard --skip-plugins --skip-themes 2>/dev/null
