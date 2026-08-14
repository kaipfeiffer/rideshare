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

        static::send_booking_notifications($booking, $riding, get_current_user_id());

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

    protected static function send_booking_notifications(array $booking, array $riding, int $booking_user_id): void
    {
        $context = static::get_booking_notification_context($booking, $riding, $booking_user_id);
        if (!$context) {
            return;
        }

        foreach (static::get_booking_notification_recipients($context) as $recipient_key => $recipient) {
            if (empty($recipient['email'])) {
                continue;
            }

            $subject = apply_filters(
                'rideshare_booking_notification_subject',
                static::get_booking_notification_subject($recipient_key, $context),
                $recipient_key,
                $context
            );

            $message = apply_filters(
                'rideshare_booking_notification_message',
                static::get_booking_notification_message($recipient_key, $recipient, $context),
                $recipient_key,
                $recipient,
                $context
            );

            wp_mail($recipient['email'], $subject, $message);
        }
    }

    protected static function get_booking_notification_context(array $booking, array $riding, int $booking_user_id): array
    {
        $is_offer = !empty($riding['driver_id']);
        $owner_id = $is_offer ? intval($riding['driver_id']) : intval($riding['passenger_id']);

        if (!$booking_user_id || !$owner_id) {
            return array();
        }

        $booking_user = get_userdata($booking_user_id);
        $owner = get_userdata($owner_id);

        if (!$booking_user || !$owner) {
            return array();
        }

        return array(
            'booking' => $booking,
            'riding' => $riding,
            'type' => $is_offer ? 'offer' : 'request',
            'type_label' => $is_offer ? __('Offer', 'rideshare') : __('Request', 'rideshare'),
            'origin_label' => Stop_Controller::get_item_label(intval($riding['origin_id'] ?? 0)),
            'destination_label' => Stop_Controller::get_item_label(intval($riding['destination_id'] ?? 0)),
            'period_label' => static::format_period_label($riding['start_date'] ?? '', $riding['end_date'] ?? ''),
            'seats' => intval($booking['seats'] ?? 1),
            'description' => $riding['description'] ?? '',
            'booking_user' => static::format_user_for_notification($booking_user),
            'owner' => static::format_user_for_notification($owner),
            'driver' => $is_offer ? static::format_user_for_notification($owner) : static::format_user_for_notification($booking_user),
            'passenger' => $is_offer ? static::format_user_for_notification($booking_user) : static::format_user_for_notification($owner),
        );
    }

    protected static function get_booking_notification_recipients(array $context): array
    {
        $recipients = array(
            'booking_user' => $context['booking_user'],
            'owner' => $context['owner'],
        );

        return apply_filters('rideshare_booking_notification_recipients', $recipients, $context);
    }

    protected static function get_booking_notification_subject(string $recipient_key, array $context): string
    {
        $route = trim(($context['origin_label'] ?? '') . ' - ' . ($context['destination_label'] ?? ''));

        if ('owner' === $recipient_key) {
            return sprintf(__('New ride booking: %s', 'rideshare'), $route);
        }

        return sprintf(__('Ride booking confirmed: %s', 'rideshare'), $route);
    }

    protected static function get_booking_notification_message(string $recipient_key, array $recipient, array $context): string
    {
        $intro = static::get_booking_notification_intro($recipient_key, $context);
        $lines = array(
            sprintf(__('Hello %s,', 'rideshare'), $recipient['name']),
            '',
            $intro,
            '',
            __('Ride details:', 'rideshare'),
            sprintf(__('Type: %s', 'rideshare'), $context['type_label']),
            sprintf(__('From: %s', 'rideshare'), $context['origin_label']),
            sprintf(__('To: %s', 'rideshare'), $context['destination_label']),
            sprintf(__('Time: %s', 'rideshare'), $context['period_label']),
            sprintf(__('Seats: %d', 'rideshare'), $context['seats']),
            sprintf(__('Driver: %s', 'rideshare'), static::format_contact_for_notification($context['driver'])),
            sprintf(__('Passenger: %s', 'rideshare'), static::format_contact_for_notification($context['passenger'])),
        );

        if (!empty($context['description'])) {
            $lines[] = sprintf(__('Note: %s', 'rideshare'), $context['description']);
        }

        $lines[] = '';
        $lines[] = __('This message was sent automatically by Rideshare.', 'rideshare');

        return implode("\n", $lines);
    }

    protected static function get_booking_notification_intro(string $recipient_key, array $context): string
    {
        if ('offer' === $context['type']) {
            if ('owner' === $recipient_key) {
                return sprintf(
                    __('%1$s booked %2$d seat(s) in your ride offer.', 'rideshare'),
                    $context['booking_user']['name'],
                    $context['seats']
                );
            }

            return __('Your booking for this ride offer has been saved.', 'rideshare');
        }

        if ('owner' === $recipient_key) {
            return sprintf(
                __('%s accepted your ride request as driver.', 'rideshare'),
                $context['booking_user']['name']
            );
        }

        return __('You accepted this ride request as driver.', 'rideshare');
    }

    protected static function format_user_for_notification(\WP_User $user): array
    {
        $name = $user->display_name ?: $user->user_login;

        return array(
            'id' => intval($user->ID),
            'name' => $name,
            'email' => $user->user_email,
        );
    }

    protected static function format_contact_for_notification(array $user): string
    {
        if (empty($user['email'])) {
            return $user['name'];
        }

        return sprintf('%s <%s>', $user['name'], $user['email']);
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
