<?php

namespace KaiPfeiffer\Rideshare;

if (!defined('WPINC')) {
    exit;
}

/**
 * Admin subpage for linked remote Rideshare instances.
 *
 * @since 0.1.1
 */
class Remote_Instance_Subpage extends Admin_List_Subpage_Abstract
{
    const ADMIN_SUBPAGE_SLUG = 'remote_instance';

    const CLASS_NAME = __CLASS__;

    const NONCE = 'Remote_Instance_Subpage_Nonce';

    static function get_page_title()
    {
        return __('Remote Instances', 'rideshare');
    }

    static function get_plural()
    {
        return __('Remote Instances', 'rideshare');
    }

    static function get_singular()
    {
        return __('Remote Instance', 'rideshare');
    }

    static function get_title()
    {
        return __('Remote Instances', 'rideshare');
    }
}
