<?php

namespace KaiPfeiffer\Rideshare;

if (!defined('WPINC')) {
    die;
}

/**
 * Rideshare defaults for admin subpages backed by WP_List_Table.
 *
 * @author  Kai Pfeiffer <kp@loworx.com>
 * @package rideshare
 * @since   1.0.0
 */

use \KaiPfeiffer\WPBase\Abstracts\AdminListSubpageAbstract;

abstract class Admin_List_Subpage_Abstract extends AdminListSubpageAbstract
{
    const ADMIN_SUBPAGE_SLUG = '';

    const AJAX_METHODS = array('ajax_response', 'ajax_rows', 'ajax_column_headers', 'ajax_navigation');

    const CLASS_NAME = '';

    const NONCE = '';

    const NONCE_FIELD = 'search_nonce';

    const SEARCH_INPUT_ID = 'search_id';

    /**
     * @var string
     * @since 1.0.39
     */
    protected static $admin_hook_suffix = null;

    /**
     * @var \WP_List_Table
     * @since 1.0.0
     */
    protected static $wp_list_table_instance = null;

    /**
     * @var Form_Table_Abstract
     */
    protected static $form_table_instance = null;

    abstract static function get_plural();

    abstract static function get_singular();

    static function get_js_handle($postfix = 'admin_subpage')
    {
        return Settings::PLUGIN_NAME . '_' . $postfix;
    }

    static function get_js_url()
    {
        return Settings::PLUGIN_URL . implode(DIRECTORY_SEPARATOR, array('includes', 'admin', 'assets', 'js', 'wp-list-class-ajax.js'));
    }

    static function get_js_version()
    {
        return Settings::PLUGIN_VERSION;
    }

    static function get_template()
    {
        return wp_unslash(Settings::PLUGIN_DIR_PATH) . '/includes/admin/templates/default-subpage-template.php';
    }
}
