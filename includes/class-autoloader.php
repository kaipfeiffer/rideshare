<?php

namespace KaiPfeiffer\Rideshare;

if (! defined('ABSPATH')) {
	die;
}

/**
 * Patient-File autoloader.
 *
 * Patient-File autoloader handler class is responsible for loading the different
 * classes needed to run the plugin.
 *
 * @author  Kai Pfeiffer <kp@loworx.com>
 * 
 * @since 0.1.0
 */
class Autoloader
	{

	/**
	 * Classes map.
	 *
	 * Maps Rideshare classes to file names.
	 *
	 * @since 0.1.0
	 * @access private
	 * @static
	 *
	 * @var array Classes used by elementor.
	 */
	private static $classes_map;

	/**
	 * Default namespace for autoloader.
	 *
	 * @since 0.1.0
	 * @access private
	 * @static
	 *
	 * @var string
	 */
	private static $default_namespace;

	/**
	 * Default namespace regex for autoloader.
	 *
	 * escaped regular expression avoid signal characters through backslashes in the namespace in regexes
	 * 
	 * @since 0.1.0
	 * @access private
	 * @static
	 *
	 * @var string
	 */
	private static $default_namespace_regex;

	/**
	 * Default path for autoloader.
	 *
	 * @since 0.1.0
	 * @access private
	 * @static
	 *
	 * @var string
	 */
	private static $default_path;


	/**
	 * Run autoloader.
	 *
	 * Register a function as `__autoload()` implementation.
	 *
	 * @access public
	 * 
	 * @param string
	 * 
	 * @since 0.1.0
	 * @static
	 */
	public static function run($default_path = '', $default_namespace = '')
	{
		if ('' === $default_path) {
			$default_path = Settings::PLUGIN_DIR_PATH;
		}
		if ('' === $default_namespace) {
			$default_namespace = __NAMESPACE__;
		}

		self::$default_namespace_regex	= str_replace('\\', '\\\\', $default_namespace);
		self::$default_namespace		= $default_namespace;
		self::$default_path				= $default_path;

		spl_autoload_register([__CLASS__, 'autoload']);
	}

	/**
	 * Get classes aliases.
	 *
	 * retrieve the classes aliases names.
	 *
	 * @access public
	 * 
	 * @return array
	 * 
	 * @since 0.1.0
	 * @static
	 *
	 */
	public static function get_classes_map()
	{
		if (!self::$classes_map) {
			self::$classes_map = array(
				'Activator'		=> implode(
					DIRECTORY_SEPARATOR,
					array('includes', 'class-activator.php')
				),
				'Admin'		=> implode(
					DIRECTORY_SEPARATOR,
					array('includes', 'admin', 'class-admin.php')
				),
				'Deactivator'	=> implode(
					DIRECTORY_SEPARATOR,
					array('includes', 'class-deactivator.php')
				),
				'Sanitize'		=> implode(
					DIRECTORY_SEPARATOR,
					array('includes', 'helpers', 'class-sanitize-helper.php')
				),
				'Update_Checker' => implode(
					DIRECTORY_SEPARATOR,
					array('includes', 'class-update-checker.php')
				),
			);
		}

		return self::$classes_map;
	}

	/**
	 * file_not_found
	 * 
	 * @param	string
	 * @access private
	 * @since  0.1.0
	 */
	private static function log_file_not_found($class_path)
	{
		throw new \Exception("File {$class_path} not Found");
	}

	/**
	 * get_file_name
	 *
	 * For a given class name, retrieve the filename for require
	 *
	 * @static
	 * @access private
	 * @since  0.1.0
	 *
	 * @param string $class_name Class name.
	 */
	private static function get_file_name($class_name)
	{
		$file_name 			= str_replace('_', '-', strtolower($class_name));

		// abstracts
		if (str_ends_with($file_name, 'abstract')) {
			$class_path =  self::$default_path . implode(
				DIRECTORY_SEPARATOR,
				array('includes', 'abstracts', 'class-' . $file_name . '.php')
			);
			return $class_path;
		}
		// Blocks
		if (str_ends_with($file_name, 'block')) {
			$class_path =  self::$default_path . implode(
				DIRECTORY_SEPARATOR,
				array('includes', 'blocks', 'class-' . $file_name . '.php')
			);
			return $class_path;
		}
		// Controllers
		if (str_ends_with($file_name, 'controller')) {
			$class_path =  self::$default_path . implode(
				DIRECTORY_SEPARATOR,
				array('includes', 'controllers', 'class-' . $file_name . '.php')
			);
			return $class_path;
		}
		// Custom Post Types
		if (str_ends_with($file_name, 'cpt')) {
			$class_path =  self::$default_path . implode(
				DIRECTORY_SEPARATOR,
				array('includes', 'custom-post-types', 'class-' . $file_name . '.php')
			);
			return $class_path;
		}
		// Dao
		if (str_ends_with($file_name, 'dao')) {
			$class_path =  self::$default_path . implode(
				DIRECTORY_SEPARATOR,
				array('includes', 'dao', 'class-' . $file_name . '.php')
			);
			return $class_path;
		}

		// Form Tables
		if (str_ends_with($file_name, 'form-table')) {
			$class_path =  self::$default_path . implode(
				DIRECTORY_SEPARATOR,
				array('includes', 'partials', 'form-tables', 'class-' . $file_name . '.php')
			);
			return $class_path;
		}
		// Handlers
		if (str_ends_with($file_name, 'handler')) {
			$class_path =  self::$default_path . implode(
				DIRECTORY_SEPARATOR,
				array('includes', 'handlers', 'class-' . $file_name . '.php')
			);
			return $class_path;
		}
		// Helpers
		if (str_ends_with($file_name, 'helper')) {
			$class_path =  self::$default_path . implode(
				DIRECTORY_SEPARATOR,
				array('includes', 'helpers', 'class-' . $file_name . '.php')
			);
			return $class_path;
		}
		// Interfaces
		if (str_ends_with($file_name, 'interface')) {
			$class_path =  self::$default_path . implode(
				DIRECTORY_SEPARATOR,
				array('includes', 'interfaces', 'class-' . $file_name . '.php')
			);
			return $class_path;
		}
		// Models
		if (str_ends_with($file_name, 'model')) {
			$class_path =  self::$default_path . implode(
				DIRECTORY_SEPARATOR,
				array('includes', 'models', 'class-' . $file_name . '.php')
			);
			return $class_path;
		}
		// Resources
		if (str_ends_with($file_name, 'resource')) {
			$class_path =  self::$default_path . implode(
				DIRECTORY_SEPARATOR,
				array('includes', 'resources', 'class-' . $file_name . '.php')
			);
			return $class_path;
		}
		// Singletons
		if (str_ends_with($file_name, 'singleton')) {
			$class_path =  self::$default_path . implode(
				DIRECTORY_SEPARATOR,
				array('includes', 'singletons', 'class-' . $file_name . '.php')
			);
			return $class_path;
		}
		// Subpages
		if (str_ends_with($file_name, 'subpage')) {
			$class_path =  self::$default_path . implode(
				DIRECTORY_SEPARATOR,
				array('includes', 'admin',  'subpages', 'class-' . $file_name . '.php')
			);
			return $class_path;
		}
		// WP List Tables, must be checked before Tables
		if (str_ends_with($file_name, 'wp-list-table')) {
			$class_path =  self::$default_path . implode(
				DIRECTORY_SEPARATOR,
				array('includes', 'partials', 'wp-list-tables', 'class-' . $file_name . '.php')
			);
			if (!is_file($class_path)) {
				static::log_file_not_found($class_path);
			}
			return $class_path;
		}

		// Tables
		if (str_ends_with($file_name, 'table')) {
			$class_path =  self::$default_path . implode(
				DIRECTORY_SEPARATOR,
				array('includes', 'tables', 'class-' . $file_name . '.php')
			);
			return $class_path;
		}
		// Traits
		if (str_ends_with($file_name, 'trait')) {
			$class_path =  self::$default_path . implode(
				DIRECTORY_SEPARATOR,
				array('includes', 'traits', 'class-' . $file_name . '.php')
			);
			return $class_path;
		}
	}

	/**
	 * Load class.
	 *
	 * For a given class name, require the class file.
	 *
	 * @access private
	 * @since 0.1.0
	 * @static
	 *
	 * @param string
	 */
	private static function load_class($relative_class_name)
	{
		$filename		= '';
		$classes_map = self::get_classes_map();

		if (isset($classes_map[$relative_class_name])) {
			$filename = self::$default_path . $classes_map[$relative_class_name];
		} else {
			$filename = self::get_file_name($relative_class_name);
		}

		if (is_readable($filename)) {
			require $filename;
		}
	}

	/**
	 * Autoload.
	 *
	 * For a given class, check if it exist and load it.
	 *
	 * @since 1.6.0
	 * @access private
	 * @static
	 *
	 * @param string
	 */
	private static function autoload($class)
	{
		// terminate method, if namespace doesn't match
		if (0 !== strpos($class, self::$default_namespace . '\\')) {
			return;
		}

		$relative_class_name = preg_replace('/^' . self::$default_namespace_regex . '\\\/', '', $class);

		$class_name = self::$default_namespace . '\\' . $relative_class_name;

		if (!class_exists($class_name)) {
			self::load_class($relative_class_name);
		}
	}
}
