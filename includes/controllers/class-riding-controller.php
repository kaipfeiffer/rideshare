<?php

namespace KaiPfeiffer\Rideshare;
if (!defined('WPINC')) {
    die;
}

/**
 * controller for ridings
 *
 * @author  Kai Pfeiffer <kp@loworx.com>
 * @package rideshare
 * @since   1.0.0 
 */

class Riding_Controller extends Controller_Abstract
{
    /** 
     * NONCE 
     * 
     * string to create an unique nonce
     */
    const NONCE = 'loworx_riding_controller_nonce';

    /**
     * $tramp_class
     * 
     * class for tramp locations
     * 
     * @var string
     */
    static protected $model_class = null;

    static function get_client_data(): array
    {
        return array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'action' => 'rideshare_save_riding_request',
            'can_create' => static::current_user_can_create_request(),
            'login_url' => wp_login_url(static::get_current_url()),
            'nonce' => wp_create_nonce('rideshare_riding_request_save'),
            'stops' => static::format_stops_for_client(Stop_Controller::get_active_items()),
            'riding_items' => static::get_riding_items(),
            'labels' => static::get_client_labels(),
        );
    }

    static function get_riding_items(): array
    {
        $items = Riding_Model::read(null);
        if (!is_array($items)) {
            return array();
        }

        $items = array_filter($items, function ($item) {
            return empty($item['deleted']) && (!empty($item['driver_id']) || !empty($item['passenger_id']));
        });

        usort($items, function ($left, $right) {
            return strcmp($left['start_date'] ?? '', $right['start_date'] ?? '');
        });

        return array_map(array(static::class, 'format_riding_for_client'), $items);
    }

    static function get_offered_items(): array
    {
        return array_values(array_filter(static::get_riding_items(), function ($item) {
            return 'offer' === ($item['type'] ?? '');
        }));
    }

    static function ajax_save_request(): void
    {
        if (!check_ajax_referer('rideshare_riding_request_save', 'nonce', false)) {
            wp_send_json_error(array('message' => __('The request could not be verified.', 'rideshare')), 403);
        }

        $result = static::save_request_from_submission();
        $payload = array(
            'message' => $result['message'] ?? '',
            'riding_items' => static::get_riding_items(),
        );

        if ('success' === ($result['type'] ?? '')) {
            wp_send_json_success($payload);
        }

        wp_send_json_error($payload, 400);
    }

    static function handle_request_submission(): array
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            return array();
        }

        $action = sanitize_key(wp_unslash($_POST['rideshare_riding_request_action'] ?? ''));
        if ('save' !== $action) {
            return array();
        }

        $nonce = sanitize_text_field(wp_unslash($_POST['rideshare_riding_request_nonce'] ?? ''));
        if (!wp_verify_nonce($nonce, 'rideshare_riding_request_save')) {
            return array('type' => 'error', 'message' => __('The request could not be verified.', 'rideshare'));
        }

        return static::save_request_from_submission();
    }

    protected static function save_request_from_submission(): array
    {
        if (!static::current_user_can_create_request()) {
            return array('type' => 'error', 'message' => __('Please sign in with a rideshare user account.', 'rideshare'));
        }

        $data = static::sanitize_request_submission();
        $errors = static::validate_request_submission($data);

        if ($errors) {
            return array('type' => 'error', 'message' => reset($errors));
        }

        $row = static::create_from_request_data($data, get_current_user_id());
        if (!$row) {
            return array('type' => 'error', 'message' => __('The request could not be saved.', 'rideshare'));
        }

        return array('type' => 'success', 'message' => __('Your request has been saved.', 'rideshare'));
    }

    static function get_request_form_values(array $result): array
    {
        $defaults = array(
            'request_type' => 'search',
            'origin_id' => 0,
            'destination_id' => 0,
            'passengers' => 1,
            'description' => '',
            'start_date' => '',
            'end_date' => '',
        );

        if ('error' !== ($result['type'] ?? '')) {
            return $defaults;
        }

        $values = array_merge($defaults, static::sanitize_request_submission());
        $values['start_date'] = static::format_datetime_input_value($values['start_date']);
        $values['end_date'] = static::format_datetime_input_value($values['end_date']);

        return $values;
    }

    static function current_user_can_create_request(): bool
    {
        $user_id = get_current_user_id();

        if (!$user_id) {
            return false;
        }

        $user = get_userdata($user_id);
        $can_create = $user && in_array('rideshare_user', (array) $user->roles, true);

        return (bool) apply_filters(
            'rideshare_riding_request_user_can_create',
            $can_create,
            $user_id
        );
    }

    protected static function get_current_url(): string
    {
        if (is_singular()) {
            return get_permalink() ?: home_url('/');
        }

        $request_uri = wp_unslash($_SERVER['REQUEST_URI'] ?? '/');

        return home_url($request_uri);
    }

    protected static function create_from_request_data(array $data, int $user_id): ?array
    {
        $columns = array(
            'origin_id' => $data['origin_id'],
            'destination_id' => $data['destination_id'],
            'passengers' => $data['passengers'],
            'description' => $data['description'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'owner' => $user_id,
            'status' => 0,
        );

        if ('offer' === $data['request_type']) {
            $columns['driver_id'] = $user_id;
        } else {
            $columns['passenger_id'] = $user_id;
        }

        return Riding_Model::create($columns);
    }

    protected static function sanitize_request_submission(): array
    {
        return array(
            'request_type' => sanitize_key(wp_unslash($_POST['rideshare_riding_request_type'] ?? 'search')),
            'origin_id' => absint(wp_unslash($_POST['rideshare_riding_origin_id'] ?? 0)),
            'destination_id' => absint(wp_unslash($_POST['rideshare_riding_destination_id'] ?? 0)),
            'passengers' => max(1, min(255, absint(wp_unslash($_POST['rideshare_riding_passengers'] ?? 1)))),
            'description' => sanitize_textarea_field(wp_unslash($_POST['rideshare_riding_description'] ?? '')),
            'start_date' => static::sanitize_datetime_submission($_POST['rideshare_riding_start_date'] ?? ''),
            'end_date' => static::sanitize_datetime_submission($_POST['rideshare_riding_end_date'] ?? ''),
        );
    }

    protected static function validate_request_submission(array $data): array
    {
        $errors = array();

        if (!in_array($data['request_type'], array('search', 'offer'), true)) {
            $errors[] = __('Please choose whether you want to search for or offer a ride.', 'rideshare');
        }

        if (!$data['origin_id'] || !Stop_Controller::item_exists($data['origin_id'])) {
            $errors[] = __('Please choose an origin.', 'rideshare');
        }

        if (!$data['destination_id'] || !Stop_Controller::item_exists($data['destination_id'])) {
            $errors[] = __('Please choose a destination.', 'rideshare');
        }

        if ($data['origin_id'] && $data['destination_id'] && $data['origin_id'] === $data['destination_id']) {
            $errors[] = __('Origin and destination must be different.', 'rideshare');
        }

        if (!$data['start_date']) {
            $errors[] = __('Please choose a start time.', 'rideshare');
        }

        if ($data['end_date'] && $data['start_date'] && strtotime($data['end_date']) < strtotime($data['start_date'])) {
            $errors[] = __('The end time must be after the start time.', 'rideshare');
        }

        return $errors;
    }

    protected static function sanitize_datetime_submission($value): string
    {
        $value = sanitize_text_field(wp_unslash($value));

        if (!$value) {
            return '';
        }

        $date = \DateTime::createFromFormat('Y-m-d\TH:i', $value);
        if (!$date) {
            return '';
        }

        return $date->format('Y-m-d H:i:s');
    }

    protected static function format_datetime_input_value(string $value): string
    {
        if (!$value) {
            return '';
        }

        $date = \DateTime::createFromFormat('Y-m-d H:i:s', $value);

        return $date ? $date->format('Y-m-d\TH:i') : '';
    }

    protected static function format_stops_for_client(array $stops): array
    {
        return array_values(array_map(function ($stop) {
            return array(
                'id' => intval($stop['id'] ?? 0),
                'title' => $stop['title'] ?: sprintf(__('Stop #%d', 'rideshare'), $stop['id']),
            );
        }, $stops));
    }

    protected static function format_riding_for_client(array $item): array
    {
        $type = !empty($item['driver_id']) ? 'offer' : 'request';

        return array(
            'id' => intval($item['id'] ?? 0),
            'type' => $type,
            'type_label' => 'offer' === $type ? __('Offer', 'rideshare') : __('Request', 'rideshare'),
            'origin_id' => intval($item['origin_id'] ?? 0),
            'origin_label' => Stop_Controller::get_item_label(intval($item['origin_id'] ?? 0)),
            'destination_id' => intval($item['destination_id'] ?? 0),
            'destination_label' => Stop_Controller::get_item_label(intval($item['destination_id'] ?? 0)),
            'passengers' => intval($item['passengers'] ?? 0),
            'description' => $item['description'] ?? '',
            'start_date' => $item['start_date'] ?? '',
            'start_label' => static::format_datetime_display_value($item['start_date'] ?? ''),
            'end_date' => $item['end_date'] ?? '',
            'end_label' => static::format_datetime_display_value($item['end_date'] ?? ''),
            'period_label' => static::format_period_label($item['start_date'] ?? '', $item['end_date'] ?? ''),
            'passengers_label' => 'offer' === $type ? __('Available seats', 'rideshare') : __('Number of passengers', 'rideshare'),
        );
    }

    protected static function format_period_label(string $start_date, string $end_date): string
    {
        $start_timestamp = static::get_datetime_timestamp($start_date);
        $end_timestamp = static::get_datetime_timestamp($end_date);

        $start_label = $start_timestamp ? static::format_compact_datetime($start_timestamp) : $start_date;
        $end_label = $end_timestamp ? static::format_compact_datetime($end_timestamp) : $end_date;

        if ($start_label && $end_label) {
            if ($start_timestamp && $end_timestamp && date_i18n('Y-m-d', $start_timestamp) === date_i18n('Y-m-d', $end_timestamp)) {
                return $start_label . ' - ' . date_i18n('H:i', $end_timestamp);
            }

            return $start_label . ' - ' . $end_label;
        }

        return $start_label ?: $end_label;
    }

    protected static function format_datetime_display_value(string $value): string
    {
        if (!$value) {
            return '';
        }

        $timestamp = static::get_datetime_timestamp($value);
        if (!$timestamp) {
            return $value;
        }

        return static::format_compact_datetime($timestamp);
    }

    protected static function get_datetime_timestamp(string $value): int
    {
        if (!$value) {
            return 0;
        }

        $timestamp = strtotime($value);

        return $timestamp ? intval($timestamp) : 0;
    }

    protected static function format_compact_datetime(int $timestamp): string
    {
        $current_year = date_i18n('Y');
        $date_year = date_i18n('Y', $timestamp);
        $format = $current_year === $date_year ? 'd.m. H:i' : 'd.m.Y H:i';

        return date_i18n($format, $timestamp);
    }

    protected static function get_client_labels(): array
    {
        return array(
            'title' => __('Ride sharing', 'rideshare'),
            'rides' => __('Rides', 'rideshare'),
            'no_items' => __('No rides are currently available.', 'rideshare'),
            'create_offer' => __('Offer a ride', 'rideshare'),
            'create_request' => __('Request a ride', 'rideshare'),
            'mode_label' => __('I want to', 'rideshare'),
            'search_label' => __('search for a ride', 'rideshare'),
            'offer_label' => __('offer a ride', 'rideshare'),
            'origin' => __('Origin', 'rideshare'),
            'origin_placeholder' => __('Choose origin', 'rideshare'),
            'destination' => __('Destination', 'rideshare'),
            'destination_placeholder' => __('Choose destination', 'rideshare'),
            'type' => __('Type', 'rideshare'),
            'start_date' => __('Start time', 'rideshare'),
            'end_date' => __('End time', 'rideshare'),
            'passengers' => __('Passengers', 'rideshare'),
            'description' => __('Note', 'rideshare'),
            'save' => __('Save', 'rideshare'),
            'cancel' => __('Cancel', 'rideshare'),
            'from' => __('From', 'rideshare'),
            'to' => __('To', 'rideshare'),
            'seats' => __('Seats', 'rideshare'),
            'login_required' => __('Please sign in with a rideshare user account.', 'rideshare'),
            'login' => __('Sign in', 'rideshare'),
            'no_stops' => __('No destinations are available yet.', 'rideshare'),
            'saving' => __('Saving...', 'rideshare'),
        );
    }
}
