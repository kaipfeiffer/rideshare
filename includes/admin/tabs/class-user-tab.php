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
class User_Tab extends Admin_Tab_Abstract{

    const ADMIN_TAB_SLUG = 'user';

    const ADMIN_TAB_LIB = 'user-tab';

    static function get_title()
    {
        return __('Users','rideshare');
    }
}