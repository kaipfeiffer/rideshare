<?php

namespace KaiPfeiffer\Rideshare;

if (! defined('ABSPATH')) {
    die;
}

/**
 * Model for ride bookings.
 *
 * @author  Kai Pfeiffer <kp@loworx.com>
 * @since   0.1.0
 */
class Booking_Model extends Model_Abstract
{
    protected static $columns = array(
        'id'            => '%d',
        'riding_id'     => '%d',
        'passenger_id'  => '%d',
        'driver_id'     => '%d',
        'seats'         => '%d',
        'status'        => '%d',
        'created'       => '%s',
        'updated'       => '%s',
        'deleted'       => '%s',
    );

    protected static $table_name = 'bookings';

    protected static function get_defaults(): array
    {
        return array(
            'seats' => 1,
            'status' => 0,
            'created' => date('Y-m-d H:i:s'),
        );
    }

    protected static function get_update_defaults(): array
    {
        return array('updated' => date('Y-m-d H:i:s'));
    }

    static function get_labels(): array
    {
        return array(
            'riding_id' => __('Ride', 'rideshare'),
            'passenger_id' => __('Passenger', 'rideshare'),
            'driver_id' => __('Driver', 'rideshare'),
            'seats' => __('Seats', 'rideshare'),
            'status' => __('Status', 'rideshare'),
        );
    }

    static function get_input_types(): array
    {
        return array(
            'riding_id' => 'number',
            'passenger_id' => 'number',
            'driver_id' => 'number',
            'seats' => 'number',
            'status' => 'number',
        );
    }

    static function get_active_booked_seats(int $riding_id): int
    {
        global $wpdb;

        $sql = $wpdb->prepare(
            "SELECT COALESCE(SUM(seats), 0)
            FROM `" . static::get_table_name() . "`
            WHERE riding_id = %d
                AND status = 0
                AND (deleted IS NULL OR deleted = '')",
            $riding_id
        );

        return intval($wpdb->get_var($sql));
    }

    static function get_active_booking_for_user(int $riding_id, int $user_id): ?array
    {
        global $wpdb;

        $sql = $wpdb->prepare(
            "SELECT *
            FROM `" . static::get_table_name() . "`
            WHERE riding_id = %d
                AND status = 0
                AND (deleted IS NULL OR deleted = '')
                AND (passenger_id = %d OR driver_id = %d)
            LIMIT 1",
            $riding_id,
            $user_id,
            $user_id
        );

        $row = $wpdb->get_row($sql, ARRAY_A);

        return is_array($row) ? $row : null;
    }

    static function has_active_booking(int $riding_id): bool
    {
        global $wpdb;

        $sql = $wpdb->prepare(
            "SELECT id
            FROM `" . static::get_table_name() . "`
            WHERE riding_id = %d
                AND status = 0
                AND (deleted IS NULL OR deleted = '')
            LIMIT 1",
            $riding_id
        );

        return (bool) $wpdb->get_var($sql);
    }

    static function db_delta()
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $table_name = static::get_table_name();

        $sql = "CREATE TABLE $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            riding_id bigint(20) UNSIGNED DEFAULT NULL,
            passenger_id bigint(20) UNSIGNED DEFAULT NULL,
            driver_id bigint(20) UNSIGNED DEFAULT NULL,
            seats tinyint(4) UNSIGNED DEFAULT 1,
            status bigint(20) UNSIGNED DEFAULT 0,
            created datetime DEFAULT NULL,
            updated datetime DEFAULT NULL,
            deleted datetime DEFAULT NULL,
            PRIMARY KEY id (id),
            KEY riding_id (riding_id),
            KEY passenger_id (passenger_id),
            KEY driver_id (driver_id),
            KEY status (status)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}
