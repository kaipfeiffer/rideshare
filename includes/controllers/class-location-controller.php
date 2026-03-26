<?php

namespace KaiPfeiffer\Rideshare;
if (!defined('WPINC')) {
    die;
}

use \Kaipfeiffer\Tramp\Controllers\LocationController;

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
}
