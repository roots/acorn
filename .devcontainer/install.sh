#!/bin/sh

set -eux

# Add a welcome message
echo '👋 Welcome to Roots Development 🌱' | sudo tee /usr/local/etc/vscode-dev-containers/first-run-notice.txt

WORKSPACE_FOLDER="${WORKSPACE_FOLDER:-"${PWD##*/}"}"

# This is the default .env file used by the application
FALLBACK_APP_ENV_FILE="${WORKSPACE_FOLDER}/.devcontainer/config/app/.env.example";
APP_ENV_FILE="${FALLBACK_APP_ENV_FILE}";

# If .env file exists in the workspace, let's use that instead
if [ ! -z ${APP_ENV+x} ] && [ -f "${WORKSPACE_FOLDER}/.devcontainer/.env.${APP_ENV}" ]; then
  APP_ENV_FILE="${WORKSPACE_FOLDER}/.devcontainer/.env.${APP_ENV}";
elif [ -f "${WORKSPACE_FOLDER}/.devcontainer/.env" ]; then
  APP_ENV_FILE="${WORKSPACE_FOLDER}/.devcontainer/.env";
fi

. "${APP_ENV_FILE}";
# source our application env vars

REPOSITORY_URL="${REPOSITORY_URL:-'https://github.com/roots/bedrock.git'}"

sudo chown -R vscode:www-data /roots
cd /roots/app

# if composer.json already exists, exit early
if [ -f "composer.json" ]; then
  exit 0
fi

# Clone the repository
git clone --depth=1 ${REPOSITORY_URL} . \
  && rm -rf .git

# if composer.json does not exist, exit with error message
if [ ! -f "composer.json" ]; then
  echo "composer.json not found in /roots/app"
  exit 1
fi

# copy .env file if APP_ENV_FILE is default, otherwise link .env file
[ "${APP_ENV_FILE}" = "${FALLBACK_APP_ENV_FILE}" ] \
  && cp "${APP_ENV_FILE}" /roots/app/.env \
  || ln -fs "${APP_ENV_FILE}" /roots/app/.env

# use `public` instead of `web` for the document root
if [ -d "web" ]; then
  mv web public
  sed -i 's/web\/wp/public\/wp/g' composer.json
  sed -i 's/web\/app/public\/app/g' composer.json
  [ -f 'wp-cli.yml' ] && sed -i 's/web/public/g' wp-cli.yml || true

  if [ -f 'config/application.php' ]; then
    sed -i 's/\/web/\/public/g' config/application.php
  elif [ -f 'bedrock/application.php' ]; then
    sed -i 's/\/web/\/public/g' bedrock/application.php
  fi
fi

cd /roots/app

# Install Composer dependencies
composer -d /roots/app install --no-progress --optimize-autoloader --prefer-dist --no-interaction

# Link the workspace folder
if cat "${WORKSPACE_FOLDER}/composer.json" | jq '.type' | grep -q wordpress-theme; then
  ln -fs "${WORKSPACE_FOLDER}" "/roots/app/public/app/themes/$(basename ${WORKSPACE_FOLDER})"
elif cat "${WORKSPACE_FOLDER}/composer.json" | jq '.type' | grep -q wordpress-plugin; then
  ln -fs "${WORKSPACE_FOLDER}" "/roots/app/public/app/plugins/$(basename ${WORKSPACE_FOLDER})"
elif cat "${WORKSPACE_FOLDER}/composer.json" | jq '.type' | grep -q wordpress-muplugin; then
  ln -fs "${WORKSPACE_FOLDER}" "/roots/app/public/app/mu-plugins/$(basename ${WORKSPACE_FOLDER})"
else
  cat /roots/app/composer.json | jq ".repositories += [{ type: \"path\", url: \"${WORKSPACE_FOLDER}\" }]" > /roots/app/composer.tmp \
  && rm /roots/app/composer.json \
  && mv /roots/app/composer.tmp /roots/app/composer.json \
  && composer require -d /roots/app $(cat "${WORKSPACE_FOLDER}/composer.json" | jq '.name' | tr -d '"') --no-interaction
fi

composer remove -d /roots/app wpackagist-theme/twentytwentythree
composer require -d /roots/app roots/soil

# Set filesystem permissions
sudo chown -R vscode:www-data /roots/app
sudo find /roots/app/ -type d -exec chmod g+s {} \;
sudo chmod g+w -R /roots/app

cd /roots

# wp-cli.yml file
cat <<WPCLI > /roots/wp-cli.yml
path: /roots/app/public/wp
server:
  docroot: /roots/app/public
WPCLI

# Install `wp dotenv` and `wp login` commands
wp package install aaemnnosttv/wp-cli-dotenv-command 2>/dev/null
wp package install aaemnnosttv/wp-cli-login-command 2>/dev/null
