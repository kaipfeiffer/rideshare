<?php

namespace KaiPfeiffer\Rideshare;

// If this file is called directly, abort.
if (! defined('ABSPATH')) {
    die;
}


/**
 * Settings
 * 
 * define settings to use in the classes
 * 
 * @author  Kai Pfeiffer <kp@loworx.com>
 * 
 * @since   0.1.0 
 */
class Settings
{

// Start Settings-Constants
const PLUGIN_DIR_PATH	= '';
const PLUGIN_NAME	= '';
const PLUGIN_PREFIX	= '';
const PLUGIN_TEXT_DOMAIN	= '';
const PLUGIN_URL	= '';
const PLUGIN_VERSION	= '0';
const REST_USER_FILTER_OPTION = 'hide_rideshare_users_in_rest';
const INSTANCE_MODE_OPTION = 'instance_mode';
const INSTANCE_UUID_OPTION = 'instance_uuid';
const INSTANCE_MODE_STANDARD = 'standard';
const INSTANCE_MODE_COLLECTOR = 'collector';
const INSTANCE_MODE_STANDARD_COLLECTOR = 'standard_collector';
// End Settings-Constants
}
