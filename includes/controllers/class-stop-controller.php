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
}
