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
README_FILE="${ROOT_DIR}/readme.txt"

read_plugin_header() {
	local header="$1"
	sed -nE "s/^[[:space:]]*\\*[[:space:]]*${header}:[[:space:]]*(.*)$/\\1/p" "${PLUGIN_FILE}" | head -n 1
}

read_readme_header() {
	local header="$1"

	if [[ ! -f "${README_FILE}" ]]; then
		return
	fi

	sed -nE "s/^${header}:[[:space:]]*(.*)$/\\1/p" "${README_FILE}" | head -n 1
}

json_string() {
	perl -MEncode=decode -MJSON::PP -0777 -ne 'print JSON::PP->new->ascii->encode(decode("UTF-8", $_))'
}

get_changelog() {
	if [[ -n "${RELEASE_CHANGELOG:-}" ]]; then
		printf '%s' "${RELEASE_CHANGELOG}"
		return
	fi

	if ! git -C "${ROOT_DIR}" rev-parse --is-inside-work-tree >/dev/null 2>&1; then
		printf 'Release %s.' "${PACKAGE_VERSION}"
		return
	fi

	local target_ref="HEAD"
	local previous_tag=""
	local range=""

	if git -C "${ROOT_DIR}" rev-parse -q --verify "refs/tags/${RELEASE_TAG}" >/dev/null; then
		target_ref="${RELEASE_TAG}"
	fi

	previous_tag="$(
		git -C "${ROOT_DIR}" tag --merged "${target_ref}" --sort=-creatordate \
			| grep -Fvx "${RELEASE_TAG}" \
			| head -n 1 \
			|| true
	)"

	if [[ -n "${previous_tag}" ]]; then
		range="${previous_tag}..${target_ref}"
	else
		range="${target_ref}"
	fi

	local changelog=""
	changelog="$(git -C "${ROOT_DIR}" log --no-merges --pretty=format:'* %s' "${range}")"

	if [[ -n "${changelog}" ]]; then
		printf '%s' "${changelog}"
	else
		printf 'Release %s.' "${PACKAGE_VERSION}"
	fi
}

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
RELEASE_TAG="${RELEASE_TAG:-v${PACKAGE_VERSION}}"
ZIP_FILE="${OUTPUT_DIR}/${PLUGIN_SLUG}-${PACKAGE_VERSION}.zip"
METADATA_FILE="${OUTPUT_DIR}/${PLUGIN_SLUG}-update.json"
DOWNLOAD_URL="${RIDESHARE_UPDATE_DOWNLOAD_URL:-https://github.com/kaipfeiffer/rideshare/releases/download/${RELEASE_TAG}/${PLUGIN_SLUG}-${PACKAGE_VERSION}.zip}"
PLUGIN_REQUIRES="$(read_plugin_header 'Requires at least')"
PLUGIN_REQUIRES_PHP="$(read_plugin_header 'Requires PHP')"
PLUGIN_HOMEPAGE="$(read_plugin_header 'Plugin URI')"
PLUGIN_DESCRIPTION="$(read_plugin_header 'Description')"
PLUGIN_TESTED="$(read_readme_header 'Tested up to')"
PLUGIN_CHANGELOG="$(get_changelog)"

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
rm -f "${ZIP_FILE}" "${METADATA_FILE}"
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
	zip -qr "${ZIP_FILE}" "${PLUGIN_SLUG}"
)

cat > "${METADATA_FILE}" <<JSON
{
  "name": "Rideshare",
  "slug": "${PLUGIN_SLUG}",
  "version": $(printf '%s' "${PACKAGE_VERSION}" | json_string),
  "download_url": $(printf '%s' "${DOWNLOAD_URL}" | json_string),
  "requires": $(printf '%s' "${PLUGIN_REQUIRES}" | json_string),
  "requires_php": $(printf '%s' "${PLUGIN_REQUIRES_PHP}" | json_string),
  "tested": $(printf '%s' "${PLUGIN_TESTED}" | json_string),
  "homepage": $(printf '%s' "${PLUGIN_HOMEPAGE}" | json_string),
  "sections": {
    "description": $(printf '%s' "${PLUGIN_DESCRIPTION}" | json_string),
    "changelog": $(printf '%s' "${PLUGIN_CHANGELOG}" | json_string)
  }
}
JSON

rm -rf "${STAGE_DIR}"

echo "${ZIP_FILE}"
echo "${METADATA_FILE}"
