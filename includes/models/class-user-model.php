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
class User_Model extends Model_Abstract
{

    /**
     * $columns
     * associative array with column names and their prepare-placeholders
     * 
     * @var array
     */
    protected static $columns = array(
        'id'                    => '%d',
        'location_id'           => '%d',
        'hub_id'                => '%d',
        'title'                 => '%s',
        'givenname'             => '%s',
        'familyname'            => '%s',
        'birthday'              => '%s',
        'email'                 => '%s',
        'phone'                 => '%s',
        'cell'                  => '%s',
        'identity_card_number'  => '%s',
        'identity_card_validity' => '%s',
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
    protected static $table_name = 'user';


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
            'title'                 => __('Title', 'rideshare'),
            'givenname'             => __('Given Name', 'rideshare'),
            'familyname'            => __('Family Name', 'rideshare'),
            'birthday'              => __('Birthday', 'rideshare'),
            'email'                 => __('Email', 'rideshare'),
            'phone'                 => __('Phone', 'rideshare'),
            'cell'                  => __('Cell', 'rideshare'),
            'identity_card_number'  => __('Identity Card Number', 'rideshare'),
            'identity_card_validity' => __('Identity Card Validity', 'rideshare'),
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
            'title'                 => 'text',
            'givenname'             => 'text',
            'familyname'            => 'text',
            'birthday'              => 'date',
            'email'                 => 'email',
            'phone'                 => 'tel',
            'cell'                  => 'tel',
            'identity_card_number'  => 'text',
            'identity_card_validity' => 'date',
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
            location_id bigint(20) UNSIGNED DEFAULT NULL,
            hub_id bigint(20) UNSIGNED DEFAULT NULL,
            title varchar(50) DEFAULT NULL,
            givenname varchar(100) DEFAULT NULL,
            familyname varchar(100) DEFAULT NULL,
            birthday date DEFAULT NULL,
            email varchar(200) DEFAULT NULL,
            phone varchar(32) DEFAULT NULL,
            cell varchar(32) DEFAULT NULL,
            identity_card_number varchar(32) DEFAULT NULL,
            identity_card_validity date DEFAULT NULL,
            created datetime DEFAULT NULL,
            updated datetime DEFAULT NULL,
            deleted datetime DEFAULT NULL,
            PRIMARY KEY id (id),
            KEY hub_id (hub_id),
            KEY familyname (familyname)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}
