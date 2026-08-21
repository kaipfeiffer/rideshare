<?php

namespace KaiPfeiffer\Rideshare;

if (!defined('ABSPATH')) {
    die;
}

/**
 * Authenticates server-to-server Rideshare requests.
 *
 * @since 0.1.0
 */
class Remote_Request_Authentication_Helper
{
    const HEADER_INSTANCE = 'X-Rideshare-Instance';

    const HEADER_TIMESTAMP = 'X-Rideshare-Timestamp';

    const HEADER_REQUEST_ID = 'X-Rideshare-Request-Id';

    const HEADER_SIGNATURE = 'X-Rideshare-Signature';

    const TIMESTAMP_TOLERANCE = 300;

    protected static $current_instance = null;

    static function authenticate_current_request(string $target, string $method)
    {
        static::$current_instance = null;

        $instance_uuid = static::get_header(static::HEADER_INSTANCE);
        $timestamp = static::get_header(static::HEADER_TIMESTAMP);
        $request_id = static::get_header(static::HEADER_REQUEST_ID);
        $signature = static::get_header(static::HEADER_SIGNATURE);

        if (!$instance_uuid || !$timestamp || !$request_id || !$signature) {
            return new \WP_Error('rideshare_remote_auth_missing', __('Remote authentication headers are missing.', 'rideshare'));
        }

        $request_time = static::parse_timestamp($timestamp);
        if (!$request_time || abs(time() - $request_time) > static::TIMESTAMP_TOLERANCE) {
            return new \WP_Error('rideshare_remote_auth_expired', __('Remote authentication timestamp is not valid.', 'rideshare'));
        }

        if (static::request_was_seen($instance_uuid, $request_id)) {
            return new \WP_Error('rideshare_remote_auth_replay', __('Remote authentication request was already used.', 'rideshare'));
        }

        $instance = Remote_Instance_Model::find_allowed_by_uuid($instance_uuid);
        if (!$instance || empty($instance['shared_secret'])) {
            return new \WP_Error('rideshare_remote_auth_forbidden', __('Remote instance is not allowed.', 'rideshare'));
        }

        $body = file_get_contents('php://input') ?: '';
        $expected_signature = static::create_signature(
            $instance['shared_secret'],
            strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET'),
            $target,
            $method,
            $timestamp,
            $request_id,
            $body
        );

        if (!hash_equals($expected_signature, $signature)) {
            return new \WP_Error('rideshare_remote_auth_invalid', __('Remote authentication signature is not valid.', 'rideshare'));
        }

        static::remember_request($instance_uuid, $request_id);
        Remote_Instance_Model::mark_seen(intval($instance['id'] ?? 0));
        static::$current_instance = $instance;

        return $instance;
    }

    static function get_current_instance(): ?array
    {
        return static::$current_instance;
    }

    static function create_signature(
        string $shared_secret,
        string $http_method,
        string $target,
        string $method,
        string $timestamp,
        string $request_id,
        string $body = ''
    ): string {
        $message = implode("\n", array(
            strtoupper($http_method),
            strtolower($target),
            strtolower($method),
            $timestamp,
            $request_id,
            hash('sha256', $body),
        ));

        return hash_hmac('sha256', $message, $shared_secret);
    }

    protected static function get_header(string $header): string
    {
        $server_key = 'HTTP_' . strtoupper(str_replace('-', '_', $header));

        return sanitize_text_field(wp_unslash($_SERVER[$server_key] ?? ''));
    }

    protected static function parse_timestamp(string $timestamp): int
    {
        if (ctype_digit($timestamp)) {
            return intval($timestamp);
        }

        $time = strtotime($timestamp);

        return $time ? intval($time) : 0;
    }

    protected static function request_was_seen(string $instance_uuid, string $request_id): bool
    {
        return (bool) get_site_transient(static::get_replay_key($instance_uuid, $request_id));
    }

    protected static function remember_request(string $instance_uuid, string $request_id): void
    {
        set_site_transient(static::get_replay_key($instance_uuid, $request_id), '1', static::TIMESTAMP_TOLERANCE);
    }

    protected static function get_replay_key(string $instance_uuid, string $request_id): string
    {
        return 'rideshare_remote_request_' . md5($instance_uuid . ':' . $request_id);
    }
}
