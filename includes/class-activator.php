<?php

namespace KaiPfeiffer\Rideshare;

// If this file is called directly, abort.
if (! defined('ABSPATH')) {
	die;
}

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @author  Kai Pfeiffer <kp@loworx.com>
 * 
 * @since   0.1.0
 */
class Activator
{
	/**
	 * $models
	 *
	 * list of all models to create tables for
	 */
	static $models	= array(
		'Location_Model',
		'Riding_Model',
		'Booking_Model',
		'User_Model',
		'Remote_Instance_Model',
		'Stop_Type_Model',
		'Stop_Model',
	);

	/**
	 * $plugin_info
	 *
	 * holds plugin information
	 *
	 * @var	array
	 */
	static $plugin_info = null;


	/**
	 * get_roles
	 *
	 * define roles and capabilities
	 *
	 * @return	array
	 *
	 * @since    0.1.0
	 * @access   protected
	 */
	static protected function get_roles()
	{
		return array(
			'rideshare_partner' => array(
				'name'        => __('Ride-Sharing Partner', 'rideshare'),
				'capabilities' => array(
					'read'                   				=> true,
					'edit_posts'             				=> true,
				),
			),
			'rideshare_user' => array(
				'name'        => __('Ride-Sharing User', 'rideshare'),
				'capabilities' => array(
					'read'                 			=> true,
				),
			),
		);
	}


	/**
	 * set_plugin_info
	 *
	 * initiates plugin information
	 *
	 * @param	string	$plugin_file
	 *
	 * @since    0.1.1
	 * @access   protected
	 */
	static protected function init_plugin_info($plugin_file)
	{
		if (!static::$plugin_info) {
			preg_match_all('/[A-Z]/', __NAMESPACE__, $matches);
			static::$plugin_info = get_plugin_data($plugin_file);
			static::$plugin_info['prefix']	= strtolower(implode('', $matches[0])) . '_';
		}
	}


	/**
	 * activate
	 *
	 * wordpress activation hook
	 *
	 * @access  public
	 * @since   0.1.0 
	 * @static
	 */
	public static function activate($plugin_file)
	{
		$roles 		= self::get_roles();
		static::run_for_sites(function () use ($roles) {
			static::set_roles($roles);
			static::db_delta();
			static::ensure_user_uuids();
		});

		$settings_class_file = plugin_dir_path(__FILE__) . 'class-settings.php';
		if (!is_file($settings_class_file)) {
			static::create_settings_file($plugin_file);
		}
	}


	/**
	 * db_delta
	 *
	 * create tables for all models
	 *
	 * @return	string
	 * 
	 * @since    0.1.1
	 * @access   protected
	 */
	protected static function set_roles($roles)
	{
		foreach ($roles as $role_key => $role_data) {
			// Create role if it doesn't exist
			if (! get_role($role_key)) {
				add_role($role_key, $role_data['name'], $role_data['capabilities']);
				if ('patient_manager' === $role_key) {
					$user = wp_get_current_user();
					$user->add_role($role_key);
				}
			} else {
				remove_role($role_key);
				add_role($role_key, $role_data['name'], $role_data['capabilities']);
			}
		}
	}


	/**
	 * db_delta
	 * 
	 * create tables for all models
	 *
	 * @return	string
	 * 
	 * @since    0.1.1
	 * @access   protected
	 */
	protected static function db_delta()
	{
		foreach (static::$models as $model) {
			$method	= array(__NAMESPACE__ . '\\' . $model, 'db_delta');
			if (is_callable($method)) {
				call_user_func($method);
			}
		}
	}

	protected static function run_for_sites(callable $callback): void
	{
		if (!is_multisite() || !function_exists('get_sites') || !function_exists('switch_to_blog')) {
			$callback();
			return;
		}

		$site_ids = get_sites(array(
			'fields' => 'ids',
			'number' => 0,
		));

		foreach ($site_ids as $site_id) {
			switch_to_blog((int) $site_id);

			try {
				$callback((int) $site_id);
			} finally {
				restore_current_blog();
			}
		}
	}


	/**
	 * create_plugin_constant
	 * 
	 * define plugin constants for settings file
	 *
	 * @return	string
	 * 
	 * @since    0.1.1
	 * @access   protected
	 */
	protected static function create_plugin_constants($plugin_file): string
	{
		$new_settings_content = 'const PLUGIN_DIR_PATH	= \'' . plugin_dir_path($plugin_file) . '\';' . "\n";
		$new_settings_content .= 'const PLUGIN_NAME	= \'' . static::$plugin_info['Name'] . '\';' . "\n";
		$new_settings_content .= 'const PLUGIN_PREFIX	= \'' . static::$plugin_info['prefix'] . '\';' . "\n";
		$new_settings_content .= 'const PLUGIN_TEXT_DOMAIN	= \'' . static::$plugin_info['TextDomain'] . '\';' . "\n";
		$new_settings_content .= 'const PLUGIN_URL	= \'' . plugin_dir_url($plugin_file) . '\';' . "\n";
		$new_settings_content .= 'const PLUGIN_VERSION	= \'' . static::$plugin_info['Version'] . '\';' . "\n";
		$new_settings_content .= 'const REST_USER_FILTER_OPTION = \'hide_rideshare_users_in_rest\';' . "\n";
		$new_settings_content .= 'const INSTANCE_MODE_OPTION = \'instance_mode\';' . "\n";
		$new_settings_content .= 'const INSTANCE_UUID_OPTION = \'instance_uuid\';' . "\n";
		$new_settings_content .= 'const INSTANCE_MODE_STANDARD = \'standard\';' . "\n";
		$new_settings_content .= 'const INSTANCE_MODE_COLLECTOR = \'collector\';' . "\n";
		$new_settings_content .= 'const INSTANCE_MODE_STANDARD_COLLECTOR = \'standard_collector\';' . "\n";

		return $new_settings_content;
	}


	/**
	 * scan_post_types
	 *
	 * Collects custom post type definitions for the generated settings file.
	 *
	 * @return	array
	 *
	 * @since    0.1.1
	 * @access   protected
	 */
	protected static function scan_post_types($plugin_file): array
	{
		$cpt_list = array();
		$custom_post_types_path = plugin_dir_path($plugin_file) . 'includes' . DIRECTORY_SEPARATOR . 'custom-post-types';

		if (!is_dir($custom_post_types_path)) {
			return $cpt_list;
		}

		$custom_post_types = scandir($custom_post_types_path);
		foreach ($custom_post_types as $file_name) {
			if (in_array($file_name, array('.', '..', 'class-sample-cpt.php'))) {
				continue;
			}

			$cpt_classname = __NAMESPACE__ . '\\' . str_replace('-', '_', ucwords(preg_replace('/\.php$/', '', preg_replace('/^class-/', '', $file_name)), '-'));
			$method = array($cpt_classname, 'get_post_type');

			if (!is_callable($method)) {
				continue;
			}

			$cpt_name = call_user_func($method);
			$definition_method = array($cpt_classname, 'get_custom_post_type_definition');
			if (is_callable($definition_method)) {
				$cpt_list[$cpt_name] = call_user_func($definition_method);
			}
		}

		return $cpt_list;
	}


	/**
	 * create_settings_file
	 * 
	 * creates the settings file width all relevant plugin information
	 * it lists
	 * 
	 * - custom-post-types
	 * 
	 * @return	void
	 * 
	 * @since    0.1.1
	 * @access   public
	 */
	public static function create_settings_file($plugin_file)
	{
		static::init_plugin_info($plugin_file);

		$settings_class_path = plugin_dir_path(__FILE__) . 'class-settings.php';

		if (is_file($settings_class_path)) {
			$settings_file_content = file_get_contents($settings_class_path);
		} else {
			$default_settings_class_path = plugin_dir_path(__FILE__) . 'class-default-settings.php';
			if (!is_file($default_settings_class_path)) {
				throw new \Exception('Default settings file not found: ' . $default_settings_class_path);
			}
			$settings_file_content = file_get_contents($default_settings_class_path);
		}

		list($header, $content)	= explode('// Start Settings-Constants', $settings_file_content);
		list($content, $footer)	= explode('// End Settings-Constants', $content);

		$new_settings_content = static::create_plugin_constants($plugin_file);
		$new_settings_content .= "\n" . '// Custom Post Types' . "\n";
		$new_settings_content .= 'const POST_TYPES = ' . var_export(static::scan_post_types($plugin_file), true) . ';';

		$new_registration_file_content = $header . '// Start Settings-Constants' . "\n" . $new_settings_content . "\n" . '// End Settings-Constants' . $footer;

		$written = file_put_contents($settings_class_path, $new_registration_file_content);

		header('refresh:0');
	}


	/**
	 * sync_settings_file
	 *
	 * Creates or updates the generated settings file when required.
	 *
	 * @return	void
	 *
	 * @since    0.1.1
	 * @access   public
	 */
	public static function sync_settings_file($plugin_file, bool $run_plugin_update = false): void
	{
		if ($run_plugin_update) {
			static::update_plugin($plugin_file);
			return;
		}

		static::create_settings_file($plugin_file);
	}


	/**
	 * update_plugin
	 * 
	 * checks for the recently installed version and applies
	 * all new changes.
	 * 
	 * @param	class
	 * @return	void
	 * 
	 * @since	0.1.1
	 * @access	protected
	 */
	public static function update_plugin($plugin_file)
	{
		static::init_plugin_info($plugin_file);

		// static::LOG_FLAGS & static::LOG_UPDATE_PLUGIN &&

		static::run_for_sites(function () {
			$version = get_option(static::$plugin_info['prefix'] . '_version', '0.1.0');

			error_log('update_plugin: ' . static::$plugin_info['prefix'] . '_version: ' . $version . ' -> ' . static::$plugin_info['Version']);
			static::db_delta();
			static::ensure_user_uuids();

			switch ($version) {
				case '0.1.1':
					// update from 0.1.0 to 0.1.1
					// static::LOG_FLAGS & static::LOG_UPDATE_PLUGIN &&
					// update process for version 0.1.1
					break;
			}
			if (version_compare($version, static::$plugin_info['Version'], '<=')) {
				update_option(static::$plugin_info['prefix'] . '_version', static::$plugin_info['Version'], false);
			}
		});

		static::create_settings_file($plugin_file);
	}

	protected static function ensure_user_uuids(): void
	{
		if (is_callable(array(__NAMESPACE__ . '\\User_Model', 'ensure_missing_uuids'))) {
			User_Model::ensure_missing_uuids();
		}

		if (is_callable(array(__NAMESPACE__ . '\\User_Controller', 'ensure_rideshare_user_uuids'))) {
			User_Controller::ensure_rideshare_user_uuids();
		}
	}


	/**
	 * deactivate
	 *
	 * wordpress activation hook
	 *
	 * @access  public
	 * @since   0.1.0 
	 * @static
	 */
	public static function deactivate() {}
}
