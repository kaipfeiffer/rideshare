<?php

namespace KaiPfeiffer\Rideshare;

if (!defined('ABSPATH')) {
    exit;
}

class User_Form_Table extends Form_Table_Abstract
{    
    protected static $filters_registered = false;
    
    public function __construct($args = array())
    {
        parent::__construct($args);

        if (static::$filters_registered) {
            return;
        }

        add_filter('kaipfeiffer_rideshare_user_form_table_location_id_autocomplete_settings', array(Location_Controller::class, 'get_location_autocomplete_settings'), 10, 3);
        add_filter('kaipfeiffer_rideshare_user_form_table_location_id_autocomplete_data_selected_label', array(Location_Controller::class, 'get_location_autocomplete_data_selected_label'), 10, 4);

        static::$filters_registered = true;
    }
}
