#!/usr/bin/env bash
set -euo pipefail
export LC_ALL=C

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
WORKSPACE_DIR="$(cd "${ROOT_DIR}/.." && pwd)"
PLUGIN_SLUG="rideshare"
PLUGIN_FILE="${ROOT_DIR}/${PLUGIN_SLUG}.php"
WPBASE_DIR="${WPBASE_DIR:-${WORKSPACE_DIR}/wpbase}"
USE_LOCAL_WPBASE="${USE_LOCAL_WPBASE:-0}"
OUTPUT_DIR="${1:-${ROOT_DIR}/.release}"
STAGE_DIR="${OUTPUT_DIR}/stage"
PACKAGE_DIR="${STAGE_DIR}/${PLUGIN_SLUG}"

if [[ ! -f "${PLUGIN_FILE}" ]]; then
	echo "Plugin file not found: ${PLUGIN_FILE}" >&2
	exit 1
fi

VERSION="$(sed -nE 's/^[[:space:]]*\* Version:[[:space:]]*([^[:space:]]+).*/\1/p' "${PLUGIN_FILE}" | head -n 1)"
if [[ -z "${VERSION}" ]]; then
	echo "Could not read plugin version from ${PLUGIN_FILE}" >&2
	exit 1
fi
PACKAGE_VERSION="${RELEASE_VERSION:-${VERSION}}"

if [[ ! -d "${ROOT_DIR}/vendor" ]]; then
	echo "Missing vendor directory. Run composer install before building a release." >&2
	exit 1
fi

if [[ ! -d "${ROOT_DIR}/build" ]]; then
	echo "Missing build directory. Build block assets before building a release." >&2
	exit 1
fi

mkdir -p "${OUTPUT_DIR}"
rm -rf "${STAGE_DIR}"
rm -f "${OUTPUT_DIR}/${PLUGIN_SLUG}-${PACKAGE_VERSION}.zip"
mkdir -p "${PACKAGE_DIR}"

rsync -a \
	--exclude='.git/' \
	--exclude='.gitignore' \
	--exclude='.release/' \
	--exclude='bin/' \
	--exclude='node_modules/' \
	--exclude='includes/class-settings.php' \
	--exclude='*.log' \
	--exclude='.DS_Store' \
	"${ROOT_DIR}/" "${PACKAGE_DIR}/"

if [[ "1" = "${USE_LOCAL_WPBASE}" && -d "${WPBASE_DIR}/src" ]]; then
	rm -rf "${PACKAGE_DIR}/vendor/kaipfeiffer/wpbase"
	mkdir -p "${PACKAGE_DIR}/vendor/kaipfeiffer/wpbase"
	rsync -a \
		--exclude='.git/' \
		--exclude='.gitignore' \
		--exclude='*.log' \
		--exclude='.DS_Store' \
		"${WPBASE_DIR}/" "${PACKAGE_DIR}/vendor/kaipfeiffer/wpbase/"
fi

if [[ "${PACKAGE_VERSION}" != "${VERSION}" ]]; then
	perl -0pi -e "s/(\\* Version:[[:space:]]*)[^\\n]+/\${1}${PACKAGE_VERSION}/" "${PACKAGE_DIR}/${PLUGIN_SLUG}.php"
	perl -0pi -e "s/(Stable tag:[[:space:]]*)[^\\n]+/\${1}${PACKAGE_VERSION}/" "${PACKAGE_DIR}/readme.txt"
	if [[ -f "${PACKAGE_DIR}/package.json" ]]; then
		perl -0pi -e "s/(\"version\":[[:space:]]*\")[^\"]+/\${1}${PACKAGE_VERSION}/" "${PACKAGE_DIR}/package.json"
	fi
fi

(
	cd "${STAGE_DIR}"
	zip -qr "${OUTPUT_DIR}/${PLUGIN_SLUG}-${PACKAGE_VERSION}.zip" "${PLUGIN_SLUG}"
)

rm -rf "${STAGE_DIR}"

echo "${OUTPUT_DIR}/${PLUGIN_SLUG}-${PACKAGE_VERSION}.zip"
