<?php

namespace KaiPfeiffer\Rideshare;

if (!defined('ABSPATH')) {
    die;
}

/**
 * Model for linked remote Rideshare instances.
 *
 * @since 0.1.0
 */
class Remote_Instance_Model extends Model_Abstract
{
    const STATUS_PENDING = 'pending';

    const STATUS_ALLOWED = 'allowed';

    const STATUS_BLOCKED = 'blocked';

    protected static $columns = array(
        'id' => '%d',
        'title' => '%s',
        'base_url' => '%s',
        'instance_uuid' => '%s',
        'shared_secret' => '%s',
        'status' => '%s',
        'mode' => '%s',
        'last_seen' => '%s',
        'created' => '%s',
        'updated' => '%s',
        'deleted' => '%s',
    );

    protected static $table_name = 'remote_instance';

    protected static function get_defaults(): array
    {
        return array(
            'status' => static::STATUS_PENDING,
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
            'title' => __('Title', 'rideshare'),
            'base_url' => __('Base URL', 'rideshare'),
            'instance_uuid' => __('Instance UUID', 'rideshare'),
            'shared_secret' => __('Shared secret', 'rideshare'),
            'status' => __('Status', 'rideshare'),
            'mode' => __('Mode', 'rideshare'),
        );
    }

    static function get_input_types(): array
    {
        return array(
            'title' => 'text',
            'base_url' => 'url',
            'instance_uuid' => 'text',
            'shared_secret' => 'text',
            'status' => 'select',
            'mode' => 'select',
        );
    }

    static function find_allowed_by_uuid(string $instance_uuid): ?array
    {
        global $wpdb;

        $instance_uuid = trim($instance_uuid);
        if (!$instance_uuid) {
            return null;
        }

        $sql = $wpdb->prepare(
            "SELECT *
            FROM `" . static::get_table_name() . "`
            WHERE instance_uuid = %s
                AND status = %s
                AND (deleted IS NULL OR deleted = '')
            LIMIT 1",
            $instance_uuid,
            static::STATUS_ALLOWED
        );

        $row = $wpdb->get_row($sql, ARRAY_A);

        return is_array($row) ? $row : null;
    }

    static function mark_seen(int $id): void
    {
        if (!$id) {
            return;
        }

        static::update(array(
            'id' => $id,
            'last_seen' => date('Y-m-d H:i:s'),
        ));
    }

    static function db_delta()
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $table_name = static::get_table_name();

        $sql = "CREATE TABLE $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            title varchar(200) DEFAULT NULL,
            base_url varchar(255) DEFAULT NULL,
            instance_uuid varchar(36) DEFAULT NULL,
            shared_secret varchar(255) DEFAULT NULL,
            status varchar(20) DEFAULT 'pending',
            mode varchar(30) DEFAULT NULL,
            last_seen datetime DEFAULT NULL,
            created datetime DEFAULT NULL,
            updated datetime DEFAULT NULL,
            deleted datetime DEFAULT NULL,
            PRIMARY KEY id (id),
            UNIQUE KEY instance_uuid (instance_uuid),
            KEY status (status),
            KEY mode (mode)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}
