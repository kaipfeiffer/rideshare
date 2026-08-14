<?php

namespace KaiPfeiffer\Rideshare;

/**
 * Plugin Name:       Rideshare
 * Description:       Rideshare connects local rides with local co-riders to reduce traffic and carbon dioxide emissions.
 * Version:           0.1.0
 * Requires at least: 5.7
 * Requires PHP:      7.3
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
	const PLUGIN_PREFIX 		= 'kprs_';

	const REST_USER_FILTER_OPTION = 'hide_rideshare_users_in_rest';

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
		$post_types = self::get_post_types();
		foreach ($post_types as $post_type => $args) {
			$plain_post_type = str_replace(Settings::PLUGIN_PREFIX, '', str_replace(Settings::PLUGIN_PREFIX, '', $post_type));
			$classname = str_replace('_', '', ucwords($plain_post_type, '_')) . '_Cpt';
			$file_path = Settings::PLUGIN_DIR_PATH . 'includes' . DIRECTORY_SEPARATOR . 'custom-post-types' . DIRECTORY_SEPARATOR . 'class-' . str_replace('_', '-', $plain_post_type) . '-cpt.php';

			if (is_file($file_path)) {

				add_action('add_meta_boxes_' . $post_type, array(__CLASS__, $classname . '__add_meta_boxes'));
				add_filter('wp_insert_post_data', array(__CLASS__, $classname . '__wp_insert_post_data'), 10, 4);
			}
		}

		add_action('edit_user_profile', array(static::class, 'Admin__show_tramp_user_data'));
		add_action('show_user_profile', array(static::class, 'Admin__show_tramp_user_data'));
		add_action('edit_user_profile_update', array(static::class, 'Admin__save_tramp_user_data'));
		add_action('admin_menu', array(static::class, 'Admin__admin_menu'));
		add_action('admin_init', array(static::class, 'Admin__init'));
		add_filter('set-screen-option', array(static::class, 'Admin__set_screen_option'), 10, 3);
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
	 * get_post_types
	 * 
	 * For performance reasons all custom post type definitions
	 * are present in the main file, because the custom post types
	 * where defined on every page request.
	 *
	 * @return	array
	 * 
	 * @since    0.1.0
	 * @access   private
	 */
	static function get_post_types(): array
	{
		$class_name = __NAMESPACE__ . '\\Settings';

		if (class_exists($class_name) && defined($class_name . '::POST_TYPES')) {
			return $class_name::POST_TYPES ?? array();
		}
		return array();
	}


	protected static function settings_file_needs_sync(string $settings_class_file): bool
	{
		if (!is_file($settings_class_file)) {
			return true;
		}

		require_once $settings_class_file;

		return version_compare(Settings::PLUGIN_VERSION, static::get_plugin_version(), '<');
	}


	protected static function plugin_update_needs_sync(): bool
	{
		$version = get_option(static::PLUGIN_PREFIX . '_version', '0.1.0');

		return version_compare($version, static::get_plugin_version(), '<');
	}


	protected static function get_plugin_version(): string
	{
		if (!function_exists('get_plugin_data')) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugin_data = get_plugin_data(__FILE__);

		return $plugin_data['Version'];
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
		static::load_textdomains();

		$post_types = self::get_post_types();

		foreach ($post_types as $post_type => $args) {
			$cpt = register_post_type($post_type, $args);
			// error_log(__CLASS__ . '->' . __LINE__ . '->' . "CPT: " . print_r($cpt, true));
		}

		static::register_blocks();
	}

	protected static function load_textdomains(): void
	{
		load_plugin_textdomain(
			'rideshare',
			false,
			dirname(plugin_basename(__FILE__)) . '/languages'
		);

		load_plugin_textdomain(
			'wpbase',
			false,
			dirname(plugin_basename(__FILE__)) . '/vendor/kaipfeiffer/wpbase/languages'
		);
	}

	static function register_blocks(): void
	{
		$block_dir = __DIR__ . '/build/rideshare';

		if (!file_exists($block_dir . '/block.json')) {
			return;
		}

		register_block_type($block_dir, array('render_callback' => array(__CLASS__, 'render_callback')));
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

			/**
			 * Composer Autoloader
			 */
			require_once plugin_dir_path(__FILE__)  . 'vendor/autoload.php';

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
	 * public_hooks
	 * 
	 * @static
	 * @since 0.1.0
	 */
	protected static function public_hooks()
	{
		add_action('init', array(__CLASS__, 'init'));
		add_action('phpmailer_init', array(__CLASS__, 'configure_sendmail_transport'));
		add_filter('wp_mail_from', array(__CLASS__, 'filter_sendmail_from_address'));
		add_filter('wp_mail_from_name', array(__CLASS__, 'filter_sendmail_from_name'));
		add_action('wp_ajax_rideshare_save_riding_request', array(static::class, 'Riding_Controller__ajax_save_request'));
		add_action('wp_ajax_nopriv_rideshare_save_riding_request', array(static::class, 'Riding_Controller__ajax_save_request'));
		add_action('wp_ajax_rideshare_book_riding', array(static::class, 'Booking_Controller__ajax_create_booking'));
		add_action('wp_ajax_nopriv_rideshare_book_riding', array(static::class, 'Booking_Controller__ajax_create_booking'));

		if (static::rideshare_rest_user_filter_enabled()) {
			add_filter('rest_user_query', array(__CLASS__, 'exclude_rideshare_users_from_rest_query'), 10, 2);
			add_filter('rest_request_before_callbacks', array(__CLASS__, 'block_rideshare_user_rest_item'), 10, 3);
		}
	}

	static function configure_sendmail_transport($phpmailer): void
	{
		if (!static::sendmail_transport_enabled()) {
			return;
		}

		if (!is_object($phpmailer) || !method_exists($phpmailer, 'isSendmail')) {
			return;
		}

		$phpmailer->isSendmail();
		$phpmailer->Sendmail = apply_filters(
			'rideshare_sendmail_path',
			ini_get('sendmail_path') ?: '/usr/sbin/sendmail -t -i',
			$phpmailer
		);
	}

	static function sendmail_transport_enabled(): bool
	{
		$enabled = defined('RIDESHARE_USE_SENDMAIL') && RIDESHARE_USE_SENDMAIL;

		if (!$enabled) {
			$env_value = getenv('RIDESHARE_USE_SENDMAIL');
			$enabled = is_string($env_value) && in_array(strtolower($env_value), array('1', 'true', 'yes', 'on'), true);
		}

		return (bool) apply_filters(
			'rideshare_sendmail_enabled',
			$enabled
		);
	}

	static function filter_sendmail_from_address($from_email): string
	{
		if (!static::sendmail_transport_enabled()) {
			return $from_email;
		}

		return apply_filters(
			'rideshare_sendmail_from_address',
			'wordpress@poolworx.local',
			$from_email
		);
	}

	static function filter_sendmail_from_name($from_name): string
	{
		if (!static::sendmail_transport_enabled()) {
			return $from_name;
		}

		return apply_filters(
			'rideshare_sendmail_from_name',
			'Poolworx',
			$from_name
		);
	}

	static function get_rest_user_filter_option_name(): string
	{
		return static::PLUGIN_PREFIX . static::REST_USER_FILTER_OPTION;
	}

	static function rideshare_rest_user_filter_enabled(): bool
	{
		return (bool) apply_filters(
			'rideshare_rest_user_filter_enabled',
			(bool) get_option(static::get_rest_user_filter_option_name(), true)
		);
	}

	static function exclude_rideshare_users_from_rest_query($prepared_args, $request)
	{
		$prepared_args['role__not_in'] = array_unique(array_merge(
			(array) ($prepared_args['role__not_in'] ?? array()),
			static::get_rideshare_user_roles()
		));

		$prepared_args['exclude'] = array_unique(array_merge(
			array_map('intval', (array) ($prepared_args['exclude'] ?? array())),
			static::get_rideshare_user_ids()
		));

		return $prepared_args;
	}

	static function block_rideshare_user_rest_item($response, $handler, $request)
	{
		if (null !== $response) {
			return $response;
		}

		if (!is_object($request) || !method_exists($request, 'get_route')) {
			return $response;
		}

		$route = $request->get_route();
		$user_id = '/wp/v2/users/me' === $route ? get_current_user_id() : intval($request['id'] ?? 0);

		if (!$user_id || !preg_match('#^/wp/v2/users/(\d+|me)$#', $route)) {
			return $response;
		}

		if (!static::is_rideshare_user($user_id)) {
			return $response;
		}

		return new \WP_Error(
			'rideshare_rest_user_hidden',
			__('User not found.', 'rideshare'),
			array('status' => 404)
		);
	}

	protected static function get_rideshare_user_roles(): array
	{
		return array(
			'rideshare_partner',
			'rideshare_user',
		);
	}

	protected static function get_rideshare_user_ids(): array
	{
		global $wpdb;

		$meta_key_like = $wpdb->esc_like('_is_tramp_user');
		$sql = $wpdb->prepare(
			"SELECT DISTINCT user_id
			FROM {$wpdb->usermeta}
			WHERE meta_key LIKE %s
				AND meta_value IN ('1', 'on', 'true', 'yes')",
			'%' . $meta_key_like
		);

		return array_map('intval', $wpdb->get_col($sql));
	}

	protected static function is_rideshare_user($user_id): bool
	{
		$user = get_userdata($user_id);

		if (!$user) {
			return false;
		}

		if (array_intersect(static::get_rideshare_user_roles(), $user->roles)) {
			return true;
		}

		return in_array($user_id, static::get_rideshare_user_ids(), true);
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
		$name_parts = explode('/', $object->name ?? '/');
		$slug = end($name_parts);

		if ('rideshare' === $slug) {
			return static::render_rideshare_widget((array) $attributes);
		}

		return '';
	}

	static function render_rideshare_widget(array $attributes = array()): string
	{
		$initial_data = Riding_Controller::get_client_data();
		$riding_items = $initial_data['riding_items'];
		$can_use = $initial_data['can_create'];
		$field_id_prefix = wp_unique_id('rideshare-riding-widget-');

		ob_start();
		include __DIR__ . '/build/rideshare/render.php';
		return ob_get_clean();
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

		$settings_class_file = plugin_dir_path(__FILE__) . 'includes' . DIRECTORY_SEPARATOR . 'class-settings.php';

		if (is_admin()) {
			$settings_file_needs_sync = self::settings_file_needs_sync($settings_class_file);
			$plugin_update_needs_sync = self::plugin_update_needs_sync();

			if ($settings_file_needs_sync || $plugin_update_needs_sync) {
				self::load_dependencies();
				Activator::sync_settings_file(__FILE__, $plugin_update_needs_sync);
			}
		}

		/**
		 * The Settings-Class
		 */
		if (is_file($settings_class_file)) {
			require_once plugin_dir_path(__FILE__) . 'includes' . DIRECTORY_SEPARATOR . 'class-settings.php';
			self::load_dependencies();

			self::public_hooks();

			if (is_admin()) {
				self::admin_hooks();
			}
		}
	}
}
RidesharePlugin::run();
