<?php

namespace KaiPfeiffer\Rideshare;

if (!defined('ABSPATH')) {
    exit;
}

class Remote_Instance_Form_Table extends Form_Table_Abstract
{
    protected static $filters_registered = false;

    public function __construct($args = array())
    {
        parent::__construct($args);

        if (static::$filters_registered) {
            return;
        }

        add_filter('kaipfeiffer_rideshare_remote_instance_form_table_status_select_options', array(Remote_Instance_Controller::class, 'get_status_select_options'), 10, 3);
        add_filter('kaipfeiffer_rideshare_remote_instance_form_table_mode_select_options', array(Remote_Instance_Controller::class, 'get_mode_select_options'), 10, 3);

        static::$filters_registered = true;
    }
}
