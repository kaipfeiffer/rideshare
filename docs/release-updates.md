# Release and Plugin Updates

Rideshare can be installed from a release ZIP and updated through the WordPress plugin update screen.

## Build a release ZIP

```sh
bin/build-release.sh
```

The script writes:

- `.release/rideshare-<version>.zip`
- `.release/rideshare-update.json`

The ZIP contains the plugin in a `rideshare/` root directory and includes runtime dependencies such as `vendor/` and `build/`. It excludes local/generated files such as `.git/`, `.release/`, `node_modules/`, and `includes/class-settings.php`.

Release builds use the dependencies installed from `composer.lock`. Local WPBase sources are not copied into the package by default. For a one-off development ZIP only, the local WPBase checkout can be overlaid with:

```sh
USE_LOCAL_WPBASE=1 bin/build-release.sh
```

Do not use that override for distributable releases.

The update metadata uses the default release tag `v<version>` and creates the changelog from commit subjects since the previous Git tag.

Override the defaults when needed:

```sh
RELEASE_TAG=v0.1.1-beta.1 bin/build-release.sh
RIDESHARE_UPDATE_DOWNLOAD_URL=https://example.test/rideshare.zip bin/build-release.sh
RELEASE_CHANGELOG='* Manual changelog entry' bin/build-release.sh
```

## Publish update metadata

Publish the generated JSON file as release asset. It looks like this:

```json
{
  "name": "Rideshare",
  "slug": "rideshare",
  "version": "0.1.1",
  "download_url": "https://github.com/kaipfeiffer/rideshare/releases/download/v0.1.1/rideshare-0.1.1.zip",
  "requires": "5.7",
  "requires_php": "7.3",
  "tested": "7.0",
  "homepage": "https://github.com/kaipfeiffer/rideshare",
  "sections": {
    "description": "Rideshare connects local rides with local co-riders.",
    "changelog": "Bug fixes and improvements."
  }
}
```

`version` and `download_url` are required.

By default, Rideshare reads this metadata from:

```text
https://github.com/kaipfeiffer/rideshare/releases/latest/download/rideshare-update.json
```

Upload the JSON file to every GitHub release with this exact asset name:

```text
rideshare-update.json
```

## Optional configuration

No `wp-config.php` change is required for the default GitHub release endpoint.

For a different endpoint, override the default metadata URL:

```php
define('RIDESHARE_UPDATE_METADATA_URL', 'https://git.example.com/rideshare/releases/latest.json');
```

For private endpoints, add a token:

```php
define('RIDESHARE_UPDATE_AUTH_TOKEN', 'your-token');
```

The token is sent as a Bearer token to the metadata URL and, during plugin updates, to the package URL.

The same values can be changed in code through filters:

- `rideshare_update_metadata_url`
- `rideshare_update_auth_token`
- `rideshare_update_request_args`

## Version workflow

1. Update the plugin header `Version` in `rideshare.php`.
2. Update `Stable tag` and changelog in `readme.txt`.
3. If the release needs WPBase changes, tag WPBase first and push the tag.
4. Update the WPBase constraint in `composer.json` if needed.
5. Run `composer update kaipfeiffer/wpbase` and commit the changed `composer.lock`.
6. Ensure build assets and `vendor/` are current.
7. Run `bin/build-release.sh`.
8. Upload the ZIP to the GitHub release.
9. Upload `.release/rideshare-update.json` to the same release.
10. WordPress will show the update in the plugin screen.

## Development setup

The Poolworx development environment mounts `../wpbase` into the Rideshare plugin vendor path through `docker-compose.yml`. This means the running development WordPress uses the current WPBase checkout even though Rideshare depends on tagged WPBase releases for installable builds.

Keep release branches reproducible by committing `composer.lock`. Track intermediate WPBase development work in separate branches, then merge/tag the desired state before updating Rideshare for a release.
