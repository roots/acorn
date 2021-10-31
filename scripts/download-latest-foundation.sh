#!/usr/bin/env bash

set  -e

# pro tip: maybe don't run this script. i mostly just hacked my way through this. this should definitely not be automated in any way.

# thx u https://explainshell.com/

# YOINK! https://stackoverflow.com/a/4774063
SCRIPTS_DIR="$(cd "$(dirname "$0")" >/dev/null 2>&1 ; pwd -P)"

TEMP_DIR="${SCRIPTS_DIR}/.temp"
FOUNDATION_DIR="${SCRIPTS_DIR}/../src/Illuminate/Foundation/"

LARAVEL_VERSION=$(curl --silent "https://api.github.com/repos/laravel/framework/tags" | jq -r '.[].name' | sort | tail -1)

ARCHIVE_FILE="${LARAVEL_VERSION}.tar.gz"

rm -rf "${TEMP_DIR}"
mkdir -p "${TEMP_DIR}"

wget -q "https://github.com/laravel/framework/archive/${ARCHIVE_FILE}" -O "${TEMP_DIR}/${ARCHIVE_FILE}"

ROOT_FOLDER=$(tar ztf "${TEMP_DIR}/${ARCHIVE_FILE}" | sort | head -1)

pushd "${TEMP_DIR}"

# only extracts the Foundation folder
tar xzf "${TEMP_DIR}/${ARCHIVE_FILE}" "${ROOT_FOLDER}src/Illuminate/Foundation" --strip-components=4 --one-top-level=Foundation

popd

rm -rf "${FOUNDATION_DIR}"
mkdir -p "${FOUNDATION_DIR}"
rsync -a "${TEMP_DIR}/Foundation/" "${FOUNDATION_DIR}"
rm -rf "${TEMP_DIR}"
