<?php

namespace KaiPfeiffer\Rideshare;

if (!defined('WPINC')) {
    die;
}

/**
 * abstract static class for admin tabs
 *
 * @author  Kai Pfeiffer <kp@loworx.com>
 * @package rideshare
 * @since   1.0.0 
 */

use  \KaiPfeiffer\WPBase\Abstracts\AdminSubpageAbstract;

abstract class Admin_Subpage_Abstract extends AdminSubpageAbstract
{
    const ADMIN_SUBPAGE_SLUG = '';


    /** 
     * AJAX_METHODS
     * 
     * Methods that could by called by the ajax router
     * 
     * @since 1.0.63
     */
    const AJAX_METHODS  = array('ajax_response');

    const CLASS_NAME    = '';

    const NONCE = '';

    const NONCE_FIELD = 'search_nonce';

    const SEARCH_INPUT_ID   = 'search_id';

    /** 
     *  $admin_hook_suffix
     * 
     *  @var string
     *  @since 1.0.39
     */
    protected static $admin_hook_suffix = null;


    /** 
     *  $admin_hook_suffix
     * 
     *  @var    \WP_LIST_TABLE
     *  @since  1.0.0
     */
    protected static $wp_list_table_instance = null;

    /**
     * $form_table_instance
     */
    protected static $form_table_instance;

    abstract static function get_plural();

    abstract static function get_singlular();

    /**
     * 
     * Settings::PLUGIN_NAME . '_admin_subpage';
     */
    static function get_js_handle($postfix = 'admin_subpage'){

        return SETTINGS::PLUGIN_NAME . '_' . $postfix;
    }

    /**
     * 
     * Settings::PLUGIN_URL . implode(DIRECTORY_SEPARATOR, array('admin', 'assets', 'js', 'wp-list-class-ajax.js'));
     */ 
    static function get_js_url(){
        $js_url  = SETTINGS::PLUGIN_URL . implode(DIRECTORY_SEPARATOR, array('admin', 'assets', 'js', 'wp-list-class-ajax.js'));
        return $js_url;
    }

    /**
     * 
     *  Settings::PLUGIN_VERSION
     */ 
    static function get_js_version(){
        return SETTINGS::PLUGIN_VERSION;
    }

    static function get_template()
    {
        return wp_unslash(Settings::PLUGIN_DIR_PATH) . '/includes/admin/templates/default_subpage_template.php';
    }
}
