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
class Stop_Model extends Model_Abstract
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
		'title'                 => '%s',
		'type'                  => '%d',
		'description'           => '%s',
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
    protected static $table_name = 'stop';


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
            'title'                 => __('Title', 'rideshare'),
            'type'                  => __('Type', 'rideshare'),
            'description'           => __('Description', 'rideshare'),
            'location_id'           => __('Location', 'rideshare'),
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
            'type'                  => 'select',
            'description'           => 'textarea',
            'location_id'           => 'autocomplete',
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

        # Diese Methode kann entfernt werden, wenn dalle Entwicklungsumgebungen angepasst sind
        static::migrate_type_titles_to_ids();

        $sql = "CREATE TABLE $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            location_id bigint(20) UNSIGNED DEFAULT NULL,
		    title varchar(100) DEFAULT NULL,
		    type bigint(20) UNSIGNED DEFAULT NULL,
		    description varchar(500) DEFAULT NULL,
            created datetime DEFAULT NULL,
            updated datetime DEFAULT NULL,
            deleted datetime DEFAULT NULL,
            PRIMARY KEY id (id),
            KEY location_id (location_id),
            KEY title (title),
            KEY type (type)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    protected static function migrate_type_titles_to_ids(): void
    {
        global $wpdb;

        $stop_table = static::get_table_name();
        $stop_type_table = Stop_Type_Model::get_table_name();

        if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $stop_table)) !== $stop_table) {
            return;
        }

        if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $stop_type_table)) !== $stop_type_table) {
            return;
        }

        $stop_types = $wpdb->get_results(
            "SELECT id, title FROM `{$stop_type_table}` WHERE deleted IS NULL OR deleted = ''",
            ARRAY_A
        );

        foreach ($stop_types as $stop_type) {
            $wpdb->update(
                $stop_table,
                array('type' => intval($stop_type['id'])),
                array('type' => $stop_type['title']),
                array('%d'),
                array('%s')
            );
        }
    }
}
