<?php

namespace KaiPfeiffer\Rideshare;

if (!defined('ABSPATH')) {
    exit;
}

use  \KaiPfeiffer\WPBase\Abstracts\FormTableAbstract;

class Form_Table_Abstract extends FormTableAbstract
{
    public function _construct($args = array())
    {   
        parent::__construct($args);
        add_filter('wpbase_no_autocomplete_settings_text', array($this, 'wpbase_no_autocomplete_settings_text_filter'), 10, 1);
    }

    public function wpbase_no_autocomplete_settings_text_filter($text)
    {
        return __('No autocomplete SETTINGS provided. Take a look at the documentation in wpbase/Setup.md.', 'wpbase');
    }
}