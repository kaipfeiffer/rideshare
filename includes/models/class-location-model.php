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
class Location_Model extends Model_Abstract
{

    /**
     * $columns
     * associative array with column names and their prepare-placeholders
     * 
     * @var array
     */
    protected static $columns = array(
        'id'        => '%d',
        'status'    => '%d',
        'street'    => '%s',
        'zipcode'   => '%s',
        'city'      => '%s',
        'region'    => '%s',
        'country'   => '%s',
        'latitude'  => '%f',
        'longitude' => '%f',
        'created'   => '%s',
        'updated'   => '%s',
        'deleted'   => '%s',
    );



    /**
     * $table_name
     * 
     * the name of the table without wp-prefix
     * 
     * @var string
     */
    protected static $table_name = 'location';


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
            'street'    => __('Street', 'rideshare'),
            'zipcode'   => __('Zip Code', 'rideshare'),
            'city'      => __('City', 'rideshare'),
            'region'    => __('Region', 'rideshare'),
            'country'   => __('Country', 'rideshare'),
            'latitude'  => __('Latitude', 'rideshare'),
            'longitude' => __('Longitude', 'rideshare'),
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
            'street'    => 'text',
            'zipcode'   => 'text',
            'city'      => 'text',
            'region'    => 'text',
            'country'   => 'text',
            'latitude'  => 'number',
            'longitude' => 'number',
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
            status bigint(20) UNSIGNED DEFAULT NULL,
            street varchar(100) DEFAULT NULL,
            zipcode varchar(10) DEFAULT NULL,
            city varchar(200) DEFAULT NULL,
            region varchar(100) DEFAULT NULL,
            country varchar(100) DEFAULT NULL,
            latitude float DEFAULT NULL,
            longitude float DEFAULT NULL,
            created datetime DEFAULT NULL,
            updated datetime DEFAULT NULL,
            deleted datetime DEFAULT NULL,
            PRIMARY KEY id (id),
            KEY `city` (`city`),
            KEY `latitude_longitude` (`latitude`,`longitude`) 
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}
