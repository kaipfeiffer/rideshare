<?php

namespace KaiPfeiffer\Rideshare;
if (!defined('WPINC')) {
    die;
}

/**
 * controller for stops
 *
 * @author  Kai Pfeiffer <kp@loworx.com>
 * @package rideshare
 * @since   1.0.0 
 */

class Stop_Controller extends Controller_Abstract
{
    /** 
     * NONCE 
     * 
     * string to create an unique nonce
     */
    const NONCE = 'loworx_stop_controller_nonce';

    /**
     * $tramp_class
     * 
     * class for tramp locations
     * 
     * @var string
     */
    static protected $model_class = null;

    static function get_active_items(): array
    {
        $stops = Stop_Model::read(null);
        if (!is_array($stops)) {
            return array();
        }

        $stops = array_filter($stops, function ($stop) {
            return empty($stop['deleted']);
        });

        usort($stops, function ($left, $right) {
            return strcasecmp($left['title'] ?? '', $right['title'] ?? '');
        });

        return $stops;
    }

    static function item_exists(int $id): bool
    {
        $stop = Stop_Model::read($id);

        return is_array($stop) && empty($stop['deleted']);
    }

    static function get_item_label(int $id): string
    {
        if (!$id) {
            return '';
        }

        $stop = Stop_Model::read($id);
        if (!is_array($stop) || !empty($stop['deleted'])) {
            return '';
        }

        return $stop['title'] ?: sprintf(__('Stop #%d', 'rideshare'), $id);
    }

    static function get_item_remote_details(int $id): array
    {
        $details = array(
            'id' => $id,
            'label' => static::get_item_label($id),
            'street' => '',
            'postal_code' => '',
            'city' => '',
            'region' => '',
            'country' => '',
            'address_label' => '',
        );

        if (!$id) {
            return $details;
        }

        $stop = Stop_Model::read($id);
        if (!is_array($stop) || !empty($stop['deleted'])) {
            return $details;
        }

        $location = array();
        if (!empty($stop['location_id'])) {
            $location = Location_Model::read(intval($stop['location_id']));
            $location = is_array($location) && empty($location['deleted']) ? $location : array();
        }

        $details['label'] = $stop['title'] ?: $details['label'];
        $details['street'] = (string) ($location['street'] ?? '');
        $details['postal_code'] = (string) ($location['zipcode'] ?? '');
        $details['city'] = (string) ($location['city'] ?? '');
        $details['region'] = (string) ($location['region'] ?? '');
        $details['country'] = (string) ($location['country'] ?? '');
        $details['address_label'] = trim(implode(', ', array_filter(array(
            trim($details['street']),
            trim(trim($details['postal_code'] . ' ' . $details['city'])),
            trim($details['country']),
        ))));

        return $details;
    }
}
