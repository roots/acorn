#!/bin/sh

set -eux

# Install volta
curl https://get.volta.sh | bash -s -- --version latest
export VOLTA_HOME="${HOME}/.volta"
export PATH="${VOLTA_HOME}/bin:${PATH}"
volta install node
volta install yarn

WORKSPACE_FOLDER="${WORKSPACE_FOLDER:-"${PWD##*/}"}"

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

yarn install || true
yarn build || true

wp db reset --yes
wp core install --url="${WP_HOME}" --title="Roots Test" --admin_user="admin" --admin_email="admin@roots.test" --admin_password="password1" --skip-email

# Add sage if there are no themes
if [ ! "$(ls -d $(wp theme path --skip-plugins --skip-themes 2>/dev/null)/*/)" ]; then
    composer require -d /roots/app roots/sage
    wp theme activate sage
fi

install_theme() {
    cd $1
    pwd
    if [ -f 'composer.json' ]; then
        composer install || true
    fi
    if [ -f 'package-lock.json' ]; then
        npm i || true
        npm run-script build || true
    elif [ -f 'package.json' ]; then
        yarn install || true
        yarn build || true
    fi
}

find $(wp theme path --skip-plugins --skip-themes 2>/dev/null) -mindepth 1 -maxdepth 1 -type d | \
    while read theme; do install_theme "$theme"; done

wp dotenv salts regenerate --skip-plugins --skip-themes 2>/dev/null || true
wp plugin activate soil --skip-plugins --skip-themes 2>/dev/null || true
wp rewrite structure '%postname%' --hard --skip-plugins --skip-themes 2>/dev/null
