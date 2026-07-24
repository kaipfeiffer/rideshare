<?php

namespace KaiPfeiffer\Rideshare;

if (!defined('WPINC')) {
    exit;
} // Exit if accessed directly


/**
 * 
 *
 * @class        
 * @version        1.0.0
 * @author        Kai Pfeiffer
 */
class Stop_Subpage extends Admin_Subpage_Abstract{

    const ADMIN_SUBPAGE_SLUG = 'stop';

    const CLASS_NAME    = __CLASS__;

    const NONCE = 'Stop_Subpage_Nonce';

    static function get_title()
    {
        return __('Stops','rideshare');
    }

    static function get_plural()
    {
        return __('Stops','rideshare');
    }

    static function get_singular()
    {
        return __('Stop','rideshare');
    }

    static function get_page_title()
    {
        return __('Stops','rideshare');
    }
}