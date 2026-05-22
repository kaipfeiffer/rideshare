<?php

namespace KaiPfeiffer\Rideshare;

// If this file is called directly, abort.
if (! defined('ABSPATH')) {
	die;
}

class Stops_Cpt extends CPT_Abstract
{
	/** 
	 * NONCE
	 * 
	 * Der String, mit dem das Nonce für die Bearbeitung der 
	 * Ticker-Texte generiert wird
	 * 
	 * @const string
	 */
	const NONCE = 'kprs_stops_nonce';

	const CLASSNAME	= __CLASS__;

	/**
	 * This is the name of this object type.
	 *
	 * @var string
	 */
	protected static $object_type = 'kprs_stops';

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected static $post_type = 'kprs_stops';


	/**
	 * Stores plugins data.
	 *
	 * @var array
	 */
	protected static $data = array(
		'kprs_name'			=> null,
		'kprs_type'			=> null,
		'kprs_description'	=> null,
		'kprs_location'		=> null,
	);


	/**
	 * create meta boxes
	 *
	 * @since    0.1.0
	 */
	public static function get_custom_post_type_definition()
	{
		return array(
			'labels'       => array(
				'name'          => __('Stops', 'rideshare'),
				'singular_name' => __('Stop', 'rideshare'),
				'menu_name'     => __('Stops', 'rideshare'),
				'add_new'       => __('Add New Stop', 'rideshare'),
				'add_new_item'  => __('Add New Stop', 'rideshare'),
				'new_item'      => __('New Stop', 'rideshare'),
				'edit_item'     => __('Edit Stop', 'rideshare'),
				'view_item'     => __('View Stop', 'rideshare'),
				'all_items'     => __('Stops', 'rideshare'),
			),
    		'capability_type'    => self::$post_type,
			'public'       => true,
			'has_archive'  => true,
			'hierarchical' => true,
			'supports'     => array('thumbnail', 'author'),
			'show_in_rest' => true,
			'show_in_menu' => RidesharePlugin::$info['Name'].'_'.Admin::ADMIN_PAGE_SLUG,
			'map_meta_cap'    => true,
		);
	}


	/**
	 * create meta boxes
	 *
	 * @since    0.1.0
	 */
	public static function add_meta_boxes()
	{
		add_meta_box(
			'_kprs_stops_properties',
			__('Stops Data', 'rideshare'),
			array(__CLASS__, 'create_view'),
			self::$post_type,
			'normal',
			'high'
		);
	}

	/**
	 * create post title from meta values
	 *
	 * @param	string
	 * @param	array
	 * @return	string
	 */
	protected static function create_post_title(string $post_title, array $postarr): string
	{
		$title = $postarr['kprs_name'] ?? '';

		return $title ?? $post_title;
	}


	/**
	 * get_ajax_params
	 *
	 * @return array
	 * @since    0.1.0
	 */
	public static function get_ajax_params()
	{
		return [
			'ajaxurl' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce(static::NONCE),
		];
	}


	/**
	 * get_data_details
	 *
	 * @return array
	 */
	protected static function get_data_details()
	{
		$labels	= self::get_labels();
		$result = array(
			'kprs_name'			=> array('type' => 'input', 'label' => $labels['kprs_name']),
			'kprs_type'			=> array('type' => 'input', 'label' => $labels['kprs_type']),
			'kprs_description'	=> array('type' => 'textarea', 'label' => $labels['kprs_description']),
			'kprs_location'		=> array('type' => 'input', 'label' => $labels['kprs_location']),
		);

		return $result;
	}


	/**
	 * Stores labels for the data.
	 *
	 * @return array
	 */
	public static function get_labels()
	{
		return array(
			'kprs_name'			=> __('Name', 'rideshare'),
			'kprs_type'			=> __('Type', 'rideshare'),
			'kprs_description'	=> __('Description', 'rideshare'),
			'kprs_location'		=> __('Location', 'rideshare'),
		);
	}
}
