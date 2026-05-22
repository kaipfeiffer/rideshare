<?php

namespace KaiPfeiffer\Rideshare;

if (!defined('ABSPATH')) {
    exit;
}



/**
 * Abstract Static Class for Database-Access via wpdb
 *
 * @since      1.0.0
 * @package    Rideshare
 * @subpackage Rideshare/includes
 * @author     Kai Pfeiffer <kp@idevo.de>
 */


use  \KaiPfeiffer\WPBase\Abstracts\ModelAbstract;

abstract class Model_Abstract extends ModelAbstract
{
    const LOG_READ = 1;

    const LOG_UPDATE = 2;

    const LOG_FLAGS = 0
            // | 1 // LOG_READ
            // | 2 // LOG_UPDATE
    ;

    /**
     * VARIABLES
     */

    /**
     * $prefix
     * the prefix of this plugin
     * 
     * @var string
     */
    protected static $prefix = 'rideshare_';


    /**
     * PRIVATE METHODS
     */

    /**
     * get_plugin_dir_path
     * 
     * @return string    the path to the plugin directory
     */
	protected static function get_plugin_dir_path(): string
	{
		return SETTINGS::PLUGIN_DIR_PATH;
	}
}
