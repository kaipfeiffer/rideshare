<?php

namespace KaiPfeiffer\Rideshare;

if (!defined('ABSPATH')) {
	exit;
}

use  \KaiPfeiffer\WPBase\Abstracts\CptAbstract;

abstract class CPT_Abstract extends CptAbstract
{



    /**
     * get_plugin_dir_path
     * 
     * @return string    the path to the plugin directory
     */
	protected static function get_plugin_dir_path(): string
	{
		return SETTINGS::PLUGIN_DIR_PATH;
	}


	/**
	 * get_plugin_url
	 * 
	 * @return string    the url to the plugin directory
	 */
	protected static function get_plugin_url(): string
	{
		return SETTINGS::PLUGIN_URL;
	}


	/**
	 * get_plugin_version
	 * 
	 * @return string    the version of the plugin
	 */
	protected static function get_plugin_version(): string
	{
		return SETTINGS::PLUGIN_VERSION;
	}
}
