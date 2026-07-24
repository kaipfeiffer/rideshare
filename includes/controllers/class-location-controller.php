<?php

namespace KaiPfeiffer\Rideshare;
if (!defined('WPINC')) {
    die;
}

/**
 * controller for locations
 *
 * @author  Kai Pfeiffer <kp@loworx.com>
 * @package rideshare
 * @since   1.0.0 
 */

class Location_Controller extends Controller_Abstract
{

    /**
     * AJAX_METHODS
     *
     * list of permitted functions, that can be called via Ajax
     */
    const AJAX_METHODS = array('get', 'post', 'search_locations');

    
    /** 
     * NONCE 
     * 
     * string to create an unique nonce
     */
    const NONCE = 'loworx_location_controller_nonce';

    /**
     * $tramp_class
     * 
     * class for tramp locations
     * 
     * @var string
     */
    static protected $model_class = null;

    static public function get_location_autocomplete_settings(array $settings, string $column_name, $form): array
    {
        $init_script_handle = strtolower(Settings::PLUGIN_NAME) . '_autocomplete_init';
        $init_script_url    = Settings::PLUGIN_URL . implode(DIRECTORY_SEPARATOR, array('public', 'asstes', 'js', 'autocomplete-init.js'));

        wp_enqueue_script(
            $init_script_handle,
            $init_script_url,
            array('jquery', 'jquery-ui-autocomplete'),
            Settings::PLUGIN_VERSION,
            true
        );

        $settings['script_handle']          = strtolower(Settings::PLUGIN_NAME) . '_location_autocomplete';
        $settings['script_url']             = Settings::PLUGIN_URL . implode(DIRECTORY_SEPARATOR, array('public', 'asstes', 'js', 'location-autocomplete.js'));
        $settings['script_path']            = Settings::PLUGIN_DIR_PATH . implode(DIRECTORY_SEPARATOR, array('public', 'asstes', 'js', 'location-autocomplete.js'));
        $settings['dependencies']           = array($init_script_handle);
        $settings['version']                = Settings::PLUGIN_VERSION;
        $settings['localize_object_name']   = 'rideshare_location_autocomplete_data';
        $settings['localize_data']          = array(
            'ajaxurl'         => admin_url('admin-ajax.php'),
            'action'          => Admin::TARGET,
            'target'          => 'search_locations',
            'class'           => 'Location_Controller',
            'nonce'           => wp_create_nonce(static::NONCE),
            'input_selector'  => '#' . $column_name . '_autocomplete',
            'value_selector'  => '#' . $column_name,
            'min_length'      => 2,
            'no_results_label' => __('No locations found.', 'rideshare'),
        );
        $settings['description']            = __('Start typing to search for an existing location.', 'rideshare');

        return $settings;
    }

    static public function get_location_autocomplete_data_selected_label(string $label, $value, string $column_name, $form): string
    {
        $location_id = intval($value);

        if (!$location_id) {
            return '';
        }

        $location = static::read($location_id);

        if (array_is_list($location)) {
            $location = array_shift($location);
        }

        if (!is_array($location)) {
            return '';
        }

        $label_parts = array_filter(array(
            $location['street'] ?? '',
            trim(($location['zipcode'] ?? '') . ' ' . ($location['city'] ?? '')),
            $location['country'] ?? '',
        ));

        return implode(', ', $label_parts);
    }

    /**
     * search_locations
     *
     * Provides location suggestions for admin autocomplete fields.
     *
     * @since    1.0.0
     */
    static public function search_locations($request)
    {
        if (!current_user_can('manage_options')) {
            return array();
        }

        if (!wp_verify_nonce($request->get('nonce', 'text'), static::NONCE)) {
            return array();
        }

        global $wpdb;

        $term       = $request->get('term', 'text') ?? '';
        $limit      = $request->get('limit', 'integer') ?: 20;
        $limit      = max(1, min(50, intval($limit)));
        $table_name = Location_Model::get_table_name();
        $like       = '%' . $wpdb->esc_like($term) . '%';

        $sql = $wpdb->prepare(
            "SELECT
                    id,
                    street,
                    zipcode,
                    city,
                    region,
                    country,
                    latitude,
                    longitude
                FROM
                    `{$table_name}`
                WHERE
                    (
                        street LIKE %s
                        OR zipcode LIKE %s
                        OR city LIKE %s
                        OR region LIKE %s
                        OR country LIKE %s
                    )
                    AND deleted IS NULL
                ORDER BY
                    city ASC,
                    street ASC
                LIMIT %d",
            $like,
            $like,
            $like,
            $like,
            $like,
            $limit
        );

        $locations = $wpdb->get_results($sql, ARRAY_A);

        return array_map(
            function ($location) {
                $label_parts = array_filter(array(
                    $location['street'] ?? '',
                    trim(($location['zipcode'] ?? '') . ' ' . ($location['city'] ?? '')),
                    $location['country'] ?? '',
                ));

                return array(
                    'id'        => intval($location['id']),
                    'value'     => implode(', ', $label_parts),
                    'label'     => implode(', ', $label_parts),
                    'location'  => $location,
                );
            },
            $locations
        );
    }
}
