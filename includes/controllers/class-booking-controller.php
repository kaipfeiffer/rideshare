<?php

namespace KaiPfeiffer\Rideshare;

if (!defined('WPINC')) {
    die;
}

/**
 * Controller for ride bookings.
 *
 * @author  Kai Pfeiffer <kp@loworx.com>
 * @package rideshare
 * @since   1.0.0
 */
class Booking_Controller extends Controller_Abstract
{
    const NONCE = 'rideshare_riding_booking';

    static protected $model_class = null;

    static function ajax_create_booking(): void
    {
        if (!check_ajax_referer(static::NONCE, 'nonce', false)) {
            wp_send_json_error(array(
                'message' => __('The booking could not be verified.', 'rideshare'),
                'riding_items' => Riding_Controller::get_riding_items(),
            ), 403);
        }

        $result = static::create_from_submission();
        $payload = array(
            'message' => $result['message'] ?? '',
            'riding_items' => Riding_Controller::get_riding_items(),
        );

        if ('success' === ($result['type'] ?? '')) {
            wp_send_json_success($payload);
        }

        wp_send_json_error($payload, 400);
    }

    static function create_from_submission(): array
    {
        if (!Riding_Controller::current_user_can_create_request()) {
            return array('type' => 'error', 'message' => __('Please sign in with a rideshare user account.', 'rideshare'));
        }

        $data = static::sanitize_submission();
        $riding = Riding_Model::read($data['riding_id']);

        $errors = static::validate_submission($data, is_array($riding) ? $riding : array(), get_current_user_id());
        if ($errors) {
            return array('type' => 'error', 'message' => reset($errors));
        }

        $booking = Booking_Model::create(static::get_booking_columns($data, $riding, get_current_user_id()));

        if (!$booking) {
            return array('type' => 'error', 'message' => __('The booking could not be saved.', 'rideshare'));
        }

        return array('type' => 'success', 'message' => __('Your booking has been saved.', 'rideshare'));
    }

    protected static function get_booking_columns(array $data, array $riding, int $user_id): array
    {
        $columns = array(
            'riding_id' => $data['riding_id'],
            'seats' => $data['seats'],
            'status' => 0,
        );

        if (!empty($riding['driver_id'])) {
            $columns['passenger_id'] = $user_id;
            return $columns;
        }

        $columns['driver_id'] = $user_id;
        $columns['seats'] = max(1, intval($riding['passengers'] ?? 1));

        return $columns;
    }

    static function get_booked_seats(int $riding_id): int
    {
        if (!$riding_id) {
            return 0;
        }

        return Booking_Model::get_active_booked_seats($riding_id);
    }

    static function user_has_active_booking(int $riding_id, int $passenger_id): bool
    {
        if (!$riding_id || !$passenger_id) {
            return false;
        }

        return null !== Booking_Model::get_active_booking_for_user($riding_id, $passenger_id);
    }

    static function riding_has_active_booking(int $riding_id): bool
    {
        if (!$riding_id) {
            return false;
        }

        return Booking_Model::has_active_booking($riding_id);
    }

    static function get_available_seats(array $riding): int
    {
        $capacity = max(0, intval($riding['passengers'] ?? 0));

        if (!$capacity || empty($riding['id'])) {
            return 0;
        }

        if (empty($riding['driver_id'])) {
            return static::riding_has_active_booking(intval($riding['id'])) ? 0 : $capacity;
        }

        return max(0, $capacity - static::get_booked_seats(intval($riding['id'])));
    }

    protected static function sanitize_submission(): array
    {
        return array(
            'riding_id' => absint(wp_unslash($_POST['rideshare_riding_id'] ?? 0)),
            'seats' => max(1, min(255, absint(wp_unslash($_POST['rideshare_booking_seats'] ?? 1)))),
        );
    }

    protected static function validate_submission(array $data, array $riding, int $user_id): array
    {
        $errors = array();

        if (!$data['riding_id'] || !$riding || !empty($riding['deleted'])) {
            $errors[] = __('Ride not found.', 'rideshare');
            return $errors;
        }

        if ($user_id && intval($riding['driver_id'] ?? 0) === $user_id) {
            $errors[] = __('You cannot book your own ride.', 'rideshare');
        }

        if ($user_id && intval($riding['passenger_id'] ?? 0) === $user_id) {
            $errors[] = __('You cannot book your own ride.', 'rideshare');
        }

        if (static::user_has_active_booking($data['riding_id'], $user_id)) {
            $errors[] = __('You have already booked this ride.', 'rideshare');
        }

        if (empty($riding['driver_id']) && static::riding_has_active_booking($data['riding_id'])) {
            $errors[] = __('This ride request has already been booked.', 'rideshare');
        }

        if (!empty($riding['driver_id']) && $data['seats'] > static::get_available_seats($riding)) {
            $errors[] = __('This ride is fully booked.', 'rideshare');
        }

        return $errors;
    }
}
