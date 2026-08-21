<?php

namespace KaiPfeiffer\Rideshare;

if (!defined('WPINC')) {
    die;
}

/**
 * Controller for local instance settings.
 *
 * @since 0.1.1
 */
class Instance_Controller extends Controller_Abstract
{
    static function get_mode_option_name(): string
    {
        return Settings::PLUGIN_PREFIX . Settings::INSTANCE_MODE_OPTION;
    }

    static function get_uuid_option_name(): string
    {
        return Settings::PLUGIN_PREFIX . Settings::INSTANCE_UUID_OPTION;
    }

    static function get_uuid(): string
    {
        $uuid = (string) static::get_option_with_legacy_fallback(
            static::get_uuid_option_name(),
            Settings::INSTANCE_UUID_OPTION,
            ''
        );

        if ($uuid) {
            return $uuid;
        }

        $uuid = User_Model::create_uuid();
        update_option(static::get_uuid_option_name(), $uuid, false);

        return $uuid;
    }

    static function get_modes(): array
    {
        return array(
            Settings::INSTANCE_MODE_STANDARD => __('Standard', 'rideshare'),
            Settings::INSTANCE_MODE_COLLECTOR => __('Collector', 'rideshare'),
            Settings::INSTANCE_MODE_STANDARD_COLLECTOR => __('Standard and collector', 'rideshare'),
        );
    }

    static function get_mode(): string
    {
        $mode = (string) static::get_option_with_legacy_fallback(
            static::get_mode_option_name(),
            Settings::INSTANCE_MODE_OPTION,
            Settings::INSTANCE_MODE_STANDARD
        );

        return array_key_exists($mode, static::get_modes()) ? $mode : Settings::INSTANCE_MODE_STANDARD;
    }

    static function can_expose_remote_rides(): bool
    {
        return in_array(
            static::get_mode(),
            array(Settings::INSTANCE_MODE_STANDARD, Settings::INSTANCE_MODE_STANDARD_COLLECTOR),
            true
        );
    }

    static function can_collect_remote_rides(): bool
    {
        return in_array(
            static::get_mode(),
            array(Settings::INSTANCE_MODE_COLLECTOR, Settings::INSTANCE_MODE_STANDARD_COLLECTOR),
            true
        );
    }

    protected static function get_legacy_option_name(string $option): string
    {
        return 'kprs_' . $option;
    }

    protected static function get_option_with_legacy_fallback(string $option_name, string $option, $default)
    {
        $value = get_option($option_name, null);
        if (null !== $value) {
            return $value;
        }

        $legacy_value = get_option(static::get_legacy_option_name($option), null);
        if (null !== $legacy_value) {
            update_option($option_name, $legacy_value, false);
            return $legacy_value;
        }

        return $default;
    }
}
