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
}
