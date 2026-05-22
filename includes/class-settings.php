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
const PLUGIN_DIR_PATH	= '/var/www/html/wp-content/plugins/rideshare/';
const PLUGIN_NAME	= 'Rideshare';
const PLUGIN_PREFIX	= 'kpr_';
const PLUGIN_TEXT_DOMAIN	= 'rideshare';
const PLUGIN_URL	= 'http://localhost:8380/local1/wp-content/plugins/rideshare/';
const PLUGIN_VERSION	= '0.1.0';

// End Settings-Constants
}
