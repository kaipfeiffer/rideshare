<?php

namespace KaiPfeiffer\Rideshare;

/**
 * Plugin Name:       Rideshare
 * Description:       Rideshare connects local rides with local co-riders to reduce traffic and carbon dioxide emissions.
 * Version:           0.1.0
 * Requires at least: 6.8
 * Requires PHP:      7.4
 * Author:            Kai Pfeiffer
 * Author URI:        https://github.com/kaipfeiffer
 * 
 * Plugin URI:		  https://github.com/kaipfeiffer/rideshare
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       rideshare
 * Domain Path:       /languages
 *
 * @package Rideshare
 */

if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}


class RidesharePlugin
{
	/**
	 * $is_loaded
	 * 
	 * Marker to check, if required files are defined
	 * 
	 * @var	bool
	 */
	static private $is_loaded = false;

	static $json_classes = array(
		__NAMESPACE__ . '\\Routing_Handler',
		__NAMESPACE__ . '\\Admin'
	);

	/**
	 * __callStatic
	 * 
	 * For performance reasons this plugin provides a mechanism to
	 * load the required classes only if a registered action or filter
	 * is fired. The name of the method contains the class name and the 
	 * required method divided by double underscore ("__").
	 * 
	 * @since   0.1.0 
	 * @static
	 */
	static function __callStatic($name, $arguments)
	{
		if (!self::$is_loaded) {
			self::load_dependencies();
		}

		$method = explode('__', $name);
		$method[0] = __NAMESPACE__ . '\\' . $method[0];
		error_log(__CLASS__ . '->' . __FUNCTION__ . '->' . __LINE__ . '-> CALLING METHOD: ' . implode('::', $method));
		if (is_callable($method)) {
			return call_user_func($method, ...$arguments);
		}
		return ($arguments[0] ?? null);
	}


	/**
	 * activate
	 * 
	 * @since   0.1.0 
	 * @static
	 */
	static function activate()
	{
		if (!self::$is_loaded) {
			self::load_dependencies();
		}

		// require plugin_dir_path(__FILE__) . 'includes' . DIRECTORY_SEPARATOR . 'class-activator.php';

		// static::LOG_FLAGS & static::LOG_ACTIVATE &&
		Activator::activate(__FILE__);
	}


	/**
	 * admin_hooks
	 * 
	 * @static
	 * @since 0.1.0
	 */
	protected static function admin_hooks()
	{
		add_action('edit_user_profile', array(static::class, 'Admin__show_tramp_user_data'));
		add_action('show_user_profile', array(static::class, 'Admin__show_tramp_user_data'));
		add_action('edit_user_profile_update', array(static::class, 'Admin__save_tramp_user_data'));
		add_action('admin_menu', array(static::class, 'Admin__admin_menu'));
		if ((defined('DOING_AJAX') && DOING_AJAX) || wp_is_json_request()) {
			foreach (static::$json_classes  as $class) {
				if (is_callable(array($class, 'init_json'))) {
					call_user_func(array($class, 'init_json'), static::use_logger());
				}
			}
		}
	}


	/**
	 * deactivate
	 * 
	 * @static
	 * @since   0.1.0 
	 */
	static function deactivate()
	{
		require plugin_dir_path(__FILE__) . 'includes' . DIRECTORY_SEPARATOR . 'class-activator.php';

		static::load_dependencies();

		Activator::deactivate();
	}


	/**
	 * init
	 * 
	 * Loads textdomain
	 * Registers the custom post types.
	 * 
	 * @static
	 * @since	0.1.0
	 */
	static function init()
	{
		// $loaded = load_plugin_textdomain('rideshare', false, dirname(plugin_basename(__FILE__)) . '/languages');
	}


	/**
	 * load_dependencies
	 * 
	 * Load the required dependencies for this plugin and
	 * setup the autoloader
	 *
	 * @since    0.1.0
	 * @access   private
	 */
	static private function load_dependencies()
	{

		if (!self::$is_loaded) {
			/**
			 * The singleton-trait
			 */
			require plugin_dir_path(__FILE__) . 'includes' . DIRECTORY_SEPARATOR . 'traits' . DIRECTORY_SEPARATOR . 'class-singleton-trait.php';

			/**
			 * The Autoloader
			 */
			require plugin_dir_path(__FILE__) . 'includes' . DIRECTORY_SEPARATOR . 'class-autoloader.php';

			error_log(is_file(plugin_dir_path(__FILE__) . 'includes' . DIRECTORY_SEPARATOR . 'class-autoloader.php') ? "Autoloader file found" : "Autoloader file not found");
			/**
			 * Provide new php-methods
			 */
			if (phpversion() < '8.1') {
				require plugin_dir_path(__FILE__) . 'includes' . DIRECTORY_SEPARATOR . 'compatibility' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'php8-functions.php';
			}

			Autoloader::run(plugin_dir_path(__FILE__));

			self::$is_loaded	= true;
		}
	}


	/**
	 * init_settings
	 * 
	 * @static
	 * @since	0.1.0
	 */
	protected static function init_settings() {}


	/**
	 * public_hooks
	 * 
	 * @static
	 * @since 0.1.0
	 */
	protected static function public_hooks()
	{
		add_action('init', array(__CLASS__, 'init'));
	}


	/**
	 * reading_list_block_init
	 * 
	 * template to register block types. Currently unsused
	 * 
	 * Registers the block using a `blocks-manifest.php` file, which improves the performance of block type registration.
	 * Behind the scenes, it also registers all assets so they can be enqueued
	 * through the block editor in the corresponding context.
	 *
	 * @see https://make.wordpress.org/core/2025/03/13/more-efficient-block-type-registration-in-6-8/
	 * @see https://make.wordpress.org/core/2024/10/17/new-block-type-registration-apis-to-improve-performance-in-wordpress-6-7/
	 */
	static function reading_list_block_init()
	{
		/**
		 * Registers the block(s) metadata from the `blocks-manifest.php` and registers the block type(s)
		 * based on the registered block metadata.
		 * Added in WordPress 6.8 to simplify the block metadata registration process added in WordPress 6.7.
		 *
		 * @see https://make.wordpress.org/core/2025/03/13/more-efficient-block-type-registration-in-6-8/
		 */
		if (function_exists('wp_register_block_types_from_metadata_collection')) {
			/**
			 * Registers the block type(s) from the `blocks-manifest.php` file.
			 * 
			 * Additional arguments for the method "register_block_type" must be
			 * injected by the filter "register_block_type_args"
			 * https://developer.wordpress.org/reference/hooks/register_block_type_args/
			 * 
			 * The template for the key 'render_callback' can be defined in the entry
			 * 'render' => 'file:./render.php', in the block.json file.
			 */

			// phpcs:ignore existence of function is checked above
			wp_register_block_types_from_metadata_collection(__DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php');
			return;
		}

		/**
		 * Registers the block(s) metadata from the `blocks-manifest.php` file.
		 * Added to WordPress 6.7 to improve the performance of block type registration.
		 *
		 * @see https://make.wordpress.org/core/2024/10/17/new-block-type-registration-apis-to-improve-performance-in-wordpress-6-7/
		 */
		if (function_exists('wp_register_block_metadata_collection')) {

			// phpcs:ignore existence of function is checked above
			wp_register_block_metadata_collection(__DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php');
		}

		/**
		 * Registers the block type(s) in the `blocks-manifest.php` file.
		 *
		 * @see https://developer.wordpress.org/reference/functions/register_block_type/
		 */
		$manifest_data = require __DIR__ . '/build/blocks-manifest.php';
		foreach (array_keys($manifest_data) as $block_type) {
			$slug = str_replace('-', '_', $block_type);
			register_block_type(__DIR__ . "/build/{$block_type}", array('render_callback' => array(__CLASS__, 'render_callback')));
		}
	}


	/**
	 * Render callback for the dynamic block.
	 * 
	 * fallback, if method "wp_register_block_types_from_metadata_collection" is not available.
	 * Currently unused
	 * 
	 * @param array $attributes Block attributes.
	 * @param string $param Block parameters.
	 * @param WP_Block_Type $object Block type object.
	 * @return string Rendered block HTML.
	 * 
	 * @static
	 * @since	0.1.0
	 */
	static function render_callback($attributes, $param, $object)
	{
		$slug = ((explode('/', $object->name ?? '/'))[0]);
		$file_name = __DIR__ . "/build/{$slug}/render.php";

		if (file_exists($file_name)) {
			ob_start();
			include $file_name;
			return ob_get_clean();
		}
	}


	/**
	 * run
	 * 
	 * @static
	 * @since 0.1.0
	 */
	static function run()
	{
		register_activation_hook(__FILE__, array(__CLASS__, 'activate'));
		register_deactivation_hook(__FILE__, array(__CLASS__, 'deactivate'));

		/**
		 * The Settings-Class
		 */
		$settings_class_file = plugin_dir_path(__FILE__) . 'includes' . DIRECTORY_SEPARATOR . 'class-settings.php';
		if (is_file($settings_class_file)) {
			require plugin_dir_path(__FILE__) . 'includes' . DIRECTORY_SEPARATOR . 'class-settings.php';

			self::public_hooks();

			if (is_admin()) {
				self::admin_hooks();
			}
		}
	}
}
RidesharePlugin::run();
