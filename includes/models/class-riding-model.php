<?php

namespace KaiPfeiffer\Rideshare;

// If this file is called directly, abort.
if (! defined('ABSPATH')) {
    die;
}

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @author  Kai Pfeiffer <kp@loworx.com>
 * 
 * @since   0.1.0
 */
class Riding_Model extends Model_Abstract
{

    /**
     * $columns
     * associative array with column names and their prepare-placeholders
     * 
     * @var array
     */
    protected static $columns = array(
        'id'                    => '%d',
        'passenger_id'          => '%d',
        'driver_id'             => '%d',
        'origin_id'             => '%d',
        'destination_id'        => '%d',
        'passengers'            => '%d',
        'description'           => '%s',
        'start_date'            => '%s',
        'end_date'              => '%s',
        'owner'                 => '%d',
        'status'                => '%d',
        'created'               => '%s',
        'updated'               => '%s',
        'deleted'               => '%s',
    );



    /**
     * $table_name
     * 
     * the name of the table without wp-prefix
     * 
     * @var string
     */
    protected static $table_name = 'riding';


    /**
     * get_defaults
     * 
     * get default values to the table columns
     * 
     * @return array    default values
     */
    protected static function get_defaults(): array
    {
        return array('created' => date('Y-m-d H:i:s'));
    }


    /**
     * get_update_defaults
     * 
     * get default update values to the table columns
     * 
     * @return array    default update values
     */
    protected static function get_update_defaults(): array
    {
        return array('updated' => date('Y-m-d H:i:s'));
    }


    /**
     * get_labels
     * 
     * returns associative array with column names and their labels
     * 
     * @return array
     * @since 0.1.0
     */
    static function get_labels(): array
    {
        return array(
            'destination_id'        => __('Destination', 'rideshare'),
            'passengers'            => __('Passengers', 'rideshare'),
            'description'           => __('Description', 'rideshare'),
            'start_date'            => __('Start Date', 'rideshare'),
            'end_date'              => __('End Date', 'rideshare'),
        );
    }


    /**
     * get_input_types
     * 
     * returns associative array with column names and their input types
     * 
     * @return array
     * @since 0.1.0
     */
    static function get_input_types(): array
    {
        return array(
                'destination_id'        => 'number',
                'passengers'            => 'number',
                'description'           => 'text',
                'start_date'            => 'datetime-local',
                'end_date'              => 'datetime-local',
        );
    }


    /**
     *  db_delta
     * 
     * creates the table
     */
    static function db_delta()
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $table_name = static::get_table_name();

        $sql = "CREATE TABLE $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            passenger_id bigint(20) UNSIGNED DEFAULT NULL,
            driver_id bigint(20) UNSIGNED DEFAULT NULL,
            origin_id bigint(20) UNSIGNED DEFAULT NULL,
            destination_id bigint(20) UNSIGNED DEFAULT NULL,
            passengers tinyint(4) UNSIGNED DEFAULT NULL,
            description varchar(100) DEFAULT NULL,
            start_date datetime DEFAULT NULL,
            end_date datetime DEFAULT NULL,
            owner bigint(20) UNSIGNED DEFAULT NULL,
            status bigint(20) UNSIGNED DEFAULT NULL,
            created datetime DEFAULT NULL,
            updated datetime DEFAULT NULL,
            deleted datetime DEFAULT NULL,
            PRIMARY KEY id (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}
