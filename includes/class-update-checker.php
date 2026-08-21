<?php

namespace KaiPfeiffer\Rideshare;

if (!defined('WPINC')) {
    die;
}

/**
 * Provides update metadata for Rideshare outside wordpress.org.
 *
 * The metadata endpoint must return JSON with at least:
 * - version
 * - download_url
 */
class Update_Checker
{
    const DEFAULT_METADATA_URL = 'https://github.com/kaipfeiffer/rideshare/releases/latest/download/rideshare-update.json';

    protected static $metadata = null;

    protected static $plugin_basename = '';

    protected static $plugin_file = '';

    static function register(string $plugin_file): void
    {
        static::$plugin_file = $plugin_file;
        static::$plugin_basename = plugin_basename($plugin_file);

        if (!static::get_metadata_url()) {
            return;
        }

        add_filter('pre_set_site_transient_update_plugins', array(static::class, 'filter_update_plugins_transient'));
        add_filter('plugins_api', array(static::class, 'filter_plugins_api'), 10, 3);
        add_filter('upgrader_pre_download', array(static::class, 'filter_upgrader_pre_download'), 10, 4);
    }

    static function filter_update_plugins_transient($transient)
    {
        if (!is_object($transient)) {
            return $transient;
        }

        $metadata = static::get_metadata();
        if (!$metadata) {
            return $transient;
        }

        if (empty($transient->response) || !is_array($transient->response)) {
            $transient->response = array();
        }

        if (empty($transient->no_update) || !is_array($transient->no_update)) {
            $transient->no_update = array();
        }

        $plugin_data = static::get_plugin_data();
        $update = static::create_update_response($metadata);

        if (version_compare($metadata['version'], $plugin_data['Version'], '>')) {
            $transient->response[static::$plugin_basename] = $update;
            return $transient;
        }

        $transient->no_update[static::$plugin_basename] = $update;

        return $transient;
    }

    static function filter_plugins_api($result, $action, $args)
    {
        $args = (object) $args;

        if ('plugin_information' !== $action || empty($args->slug) || 'rideshare' !== $args->slug) {
            return $result;
        }

        $metadata = static::get_metadata();
        if (!$metadata) {
            return $result;
        }

        $plugin_data = static::get_plugin_data();

        return (object) array(
            'name' => $metadata['name'] ?? $plugin_data['Name'],
            'slug' => 'rideshare',
            'version' => $metadata['version'],
            'author' => $metadata['author'] ?? $plugin_data['Author'],
            'homepage' => $metadata['homepage'] ?? $plugin_data['PluginURI'],
            'requires' => $metadata['requires'] ?? $plugin_data['RequiresWP'],
            'requires_php' => $metadata['requires_php'] ?? $plugin_data['RequiresPHP'],
            'tested' => $metadata['tested'] ?? '',
            'last_updated' => $metadata['last_updated'] ?? '',
            'sections' => static::get_sections($metadata),
            'download_link' => $metadata['download_url'],
        );
    }

    static function filter_upgrader_pre_download($reply, $package, $upgrader, $hook_extra)
    {
        if (!static::get_auth_token()) {
            return $reply;
        }

        $hook_extra = is_array($hook_extra) ? $hook_extra : array();

        if (empty($hook_extra['plugin']) || static::$plugin_basename !== $hook_extra['plugin']) {
            return $reply;
        }

        $metadata = static::get_metadata();
        if (!$metadata || $package !== $metadata['download_url']) {
            return $reply;
        }

        return static::download_package($package);
    }

    protected static function create_update_response(array $metadata): object
    {
        $plugin_data = static::get_plugin_data();

        return (object) array(
            'id' => static::$plugin_basename,
            'slug' => 'rideshare',
            'plugin' => static::$plugin_basename,
            'new_version' => $metadata['version'],
            'url' => $metadata['homepage'] ?? $plugin_data['PluginURI'],
            'package' => $metadata['download_url'],
            'requires' => $metadata['requires'] ?? $plugin_data['RequiresWP'],
            'requires_php' => $metadata['requires_php'] ?? $plugin_data['RequiresPHP'],
            'tested' => $metadata['tested'] ?? '',
        );
    }

    protected static function get_sections(array $metadata): array
    {
        if (!empty($metadata['sections']) && is_array($metadata['sections'])) {
            return $metadata['sections'];
        }

        return array(
            'description' => $metadata['description'] ?? __('Rideshare plugin update.', 'rideshare'),
            'changelog' => $metadata['changelog'] ?? '',
        );
    }

    protected static function get_metadata(): ?array
    {
        if (null !== static::$metadata) {
            return static::$metadata;
        }

        $url = static::get_metadata_url();
        if (!$url) {
            static::$metadata = array();
            return null;
        }

        $response = wp_remote_get($url, static::get_request_args());
        if (is_wp_error($response)) {
            static::$metadata = array();
            return null;
        }

        if (200 !== wp_remote_retrieve_response_code($response)) {
            static::$metadata = array();
            return null;
        }

        $metadata = json_decode(wp_remote_retrieve_body($response), true);
        if (!is_array($metadata) || empty($metadata['version']) || empty($metadata['download_url'])) {
            static::$metadata = array();
            return null;
        }

        static::$metadata = $metadata;

        return static::$metadata;
    }

    protected static function get_metadata_url(): string
    {
        $url = defined('RIDESHARE_UPDATE_METADATA_URL') ? RIDESHARE_UPDATE_METADATA_URL : static::DEFAULT_METADATA_URL;

        return esc_url_raw(apply_filters('rideshare_update_metadata_url', $url));
    }

    protected static function get_auth_token(): string
    {
        $token = defined('RIDESHARE_UPDATE_AUTH_TOKEN') ? RIDESHARE_UPDATE_AUTH_TOKEN : '';

        return (string) apply_filters('rideshare_update_auth_token', $token);
    }

    protected static function get_request_args(): array
    {
        $args = array('timeout' => 15);
        $token = static::get_auth_token();

        if ($token) {
            $args['headers'] = array('Authorization' => 'Bearer ' . $token);
        }

        return apply_filters('rideshare_update_request_args', $args);
    }

    protected static function download_package(string $package)
    {
        $tmp_file = wp_tempnam($package);
        if (!$tmp_file) {
            return new \WP_Error('rideshare_update_temp_file_failed', __('Could not create a temporary update file.', 'rideshare'));
        }

        $response = wp_remote_get(
            $package,
            array_merge(
                static::get_request_args(),
                array(
                    'filename' => $tmp_file,
                    'stream' => true,
                    'timeout' => 300,
                )
            )
        );

        if (is_wp_error($response)) {
            @unlink($tmp_file);
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code($response);
        if (200 !== $status_code) {
            @unlink($tmp_file);
            return new \WP_Error(
                'rideshare_update_download_failed',
                sprintf(__('The update package could not be downloaded. HTTP status: %d', 'rideshare'), $status_code)
            );
        }

        return $tmp_file;
    }

    protected static function get_plugin_data(): array
    {
        if (!function_exists('get_plugin_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        return get_plugin_data(static::$plugin_file);
    }
}
