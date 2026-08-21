<?php

namespace KaiPfeiffer\Rideshare;

if (!defined('WPINC')) {
    die;
}

/**
 * Controller for linked remote Rideshare instances.
 *
 * @since 0.1.1
 */
class Remote_Instance_Controller extends Controller_Abstract
{
    const NONCE = 'loworx_remote_instance_controller_nonce';

    static protected $model_class = null;

    static function create($data)
    {
        return parent::create(static::prepare_data($data, true));
    }

    static function update($data)
    {
        return parent::update(static::prepare_data($data, false));
    }

    static function get_title($columns): string
    {
        if (!is_array($columns)) {
            return '';
        }

        return ($columns['title'] ?? '') ?: ($columns['base_url'] ?? '');
    }

    static function get_statuses(): array
    {
        return array(
            Remote_Instance_Model::STATUS_PENDING => __('Pending', 'rideshare'),
            Remote_Instance_Model::STATUS_ALLOWED => __('Allowed', 'rideshare'),
            Remote_Instance_Model::STATUS_BLOCKED => __('Blocked', 'rideshare'),
        );
    }

    static function get_status_select_options(array $options, string $column_name, $form): array
    {
        return static::get_statuses();
    }

    static function get_mode_select_options(array $options, string $column_name, $form): array
    {
        return Instance_Controller::get_modes();
    }

    static function get_status_label($value): string
    {
        $statuses = static::get_statuses();

        return $statuses[$value] ?? (string) $value;
    }

    static function get_mode_label($value): string
    {
        $modes = Instance_Controller::get_modes();

        return $modes[$value] ?? (string) $value;
    }

    protected static function prepare_data(array $data, bool $is_new): array
    {
        $data['title'] = sanitize_text_field($data['title'] ?? '');
        $data['base_url'] = esc_url_raw($data['base_url'] ?? '');
        $data['instance_uuid'] = sanitize_text_field($data['instance_uuid'] ?? '');
        $data['shared_secret'] = sanitize_text_field($data['shared_secret'] ?? '');
        $data['status'] = sanitize_key($data['status'] ?? Remote_Instance_Model::STATUS_PENDING);
        $data['mode'] = sanitize_key($data['mode'] ?? Settings::INSTANCE_MODE_STANDARD);

        if (!$data['instance_uuid']) {
            $data['instance_uuid'] = User_Model::create_uuid();
        }

        if ($is_new && !$data['shared_secret']) {
            $data['shared_secret'] = static::create_shared_secret();
        }

        if (!array_key_exists($data['status'], static::get_statuses())) {
            $data['status'] = Remote_Instance_Model::STATUS_PENDING;
        }

        if (!array_key_exists($data['mode'], Instance_Controller::get_modes())) {
            $data['mode'] = Settings::INSTANCE_MODE_STANDARD;
        }

        return $data;
    }

    protected static function create_shared_secret(): string
    {
        if (function_exists('random_bytes')) {
            return bin2hex(random_bytes(32));
        }

        if (function_exists('wp_generate_password')) {
            return wp_generate_password(64, false, false);
        }

        return md5(uniqid('', true)) . md5(mt_rand());
    }
}
