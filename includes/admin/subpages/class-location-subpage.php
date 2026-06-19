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
class Location_Subpage extends Admin_Subpage_Abstract
{

    const ADMIN_SUBPAGE_SLUG = 'location';

    const CLASS_NAME    = __CLASS__;

    const NONCE = 'Location_Subpage_Nonce';

    static function get_page_title()
    {
        return __('Locations', 'rideshare');
    }

    static function get_plural()
    {
        return __('Locations', 'rideshare');
    }

    static function get_singular()
    {
        return __('Location', 'rideshare');
    }
    
    static function get_title()
    {
        return __('Locations', 'rideshare');
    }
}
