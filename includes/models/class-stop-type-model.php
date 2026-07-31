<?php

namespace KaiPfeiffer\Rideshare;

if (! defined('ABSPATH')) {
    die;
}

/**
 * Model for selectable stop types.
 *
 * @author  Kai Pfeiffer <kp@loworx.com>
 * @since   0.1.0
 */
class Stop_Type_Model extends Model_Abstract
{
    protected static $columns = array(
        'id'        => '%d',
        'title'     => '%s',
        'created'   => '%s',
        'updated'   => '%s',
        'deleted'   => '%s',
    );

    protected static $table_name = 'stop_type';

    protected static function get_defaults(): array
    {
        return array('created' => date('Y-m-d H:i:s'));
    }

    protected static function get_update_defaults(): array
    {
        return array('updated' => date('Y-m-d H:i:s'));
    }

    static function get_labels(): array
    {
        return array(
            'title' => __('Title', 'rideshare'),
        );
    }

    static function get_input_types(): array
    {
        return array(
            'title' => 'text',
        );
    }

    static function get_default_titles(): array
    {
        return array(
            'gewerblich',
            'Haltestelle',
            'touristisch',
            'privat',
        );
    }

    static function db_delta()
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $table_name = static::get_table_name();

        $sql = "CREATE TABLE $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            title varchar(100) DEFAULT NULL,
            created datetime DEFAULT NULL,
            updated datetime DEFAULT NULL,
            deleted datetime DEFAULT NULL,
            PRIMARY KEY id (id),
            UNIQUE KEY title (title)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        static::seed_default_titles();
    }

    protected static function seed_default_titles(): void
    {
        foreach (static::get_default_titles() as $title) {
            $existing_rows = static::read(array('title' => $title));

            if (!$existing_rows) {
                static::create(array('title' => $title));
            }
        }
    }
}
