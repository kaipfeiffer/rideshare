<?php

namespace KaiPfeiffer\Rideshare;

use WP_List_Table;

if (!defined('WPINC')) {
    exit;
} // Exit if accessed directly


/**
 * 
 * @class        Admin
 * @version        1.0.0
 * @author        Kai Pfeiffer
 */

use KaiPfeiffer\WPBase\Interfaces\AjaxInterface;
class Admin implements AjaxInterface
{


    /** 
     * TARGET
     * 
     * Das Ajax-Ziel über das die Registrierung erfolgt
     * 
     * @const string
     */
    const TARGET    = 'rideshare-admin-router';


    /** 
     * AJAX_METHODS
     * 
     * Methods that could by called by the ajax router
     * 
     * @since 1.0.63
     */
    const AJAX_METHODS  = array('get_template');


    /**
     * ADMIN_PAGE_SLUG 
     * 
     * slug constant
     */
    const ADMIN_PAGE_SLUG  = 'rideshare_admin';


    /** 
     * NONCE 
     * 
     * string for identifying the nonce
     * 
     * @since   1.0.80
     */
    const NONCE = 'rideshare_Admin';



    /** 
     *  $admin_hook_suffix
     * 
     *  @var string
     *  @since 1.0.39
     */
    protected static $admin_hook_suffix = null;

    /** 
     *  $admin_page_tabs
     * 
     *  @var array
     *  @since 1.0.39
     */
    protected static $admin_page_tabs = [];

    /** 
     *  $blog_id
     * 
     *  Die ID des Blogs, in dem die Einstellungen gespeichert werden sollen
     *  @var int
     *  @since 1.0.39
     */
    protected static $blog_id = null;

    /** 
     *  $plugin_option_slug
     * 
     *  @var string
     *  @since 1.0.105
     */
    protected static $plugin_option_slug;


    /** 
     *  $tabs
     * 
     * Die tabs, die im Adminbereich eingebunden werden sollen
     *  @var array
     *  @since 1.0.39
     */
    protected static $tabs = array('User_Tab', 'Location_Tab');


    /** 
     *  $sub_pages
     * 
     * Die Sub_pages, die im Adminbereich eingebunden werden sollen
     *  @var array
     *  @since 1.0.39
     */
    protected static $sub_pages = array('User_Subpage', 'Location_Subpage');

    /*
    *   PROTECTED METHODS
    */

    /**
     * get_blog_id
     * 
     * Gibt die Blog-ID zurück, zu welscher die Einstellungen gespeichert werden sollen
     * 
     * @return int
     */
    static protected function get_blog_id()
    {
        global $wpdb;
        if (null === static::$blog_id) {
            static::$blog_id = get_current_blog_id();
        }
        return $wpdb->prefix . static::$blog_id;
    }


    /**
     * get_class_name
     * 
     * Ermittelt den Klassennamen aus einem Page-Slug
     *
     * @since    1.0.0
     */
    protected static function get_class_name($slug)
    {
        $slug_parts = explode('_', $slug);

        foreach ($slug_parts as $index => $slug) {
            $slug_parts[$index] = ucfirst($slug);
        }

        $class_name = implode('_', $slug_parts);

        return $class_name;
    }

    /**
     * get_is_tramp_user_meta
     * 
     * Gibt zurück, ob der Benutzer ein Tramp-User ist
     * 
     * @param WP_User $user
     * @return bool
     */
    static protected function get_is_tramp_user_meta($user_id)
    {
        $blog_id = static::get_blog_id();
        return get_user_meta($user_id, $blog_id . '_is_tramp_user', true);
    }

    static protected function get_labels()
    {
        $labels = array(
            'title' => __('Title', 'rideshare'),
            'givenname' => __('Given Name', 'rideshare'),
            'familyname'    => __('Family Name', 'rideshare'),
            'birthday'  => __('Birthday', 'rideshare'),
            'email' => __('Email', 'rideshare'),
            'phone' => __('Phone', 'rideshare'),
            'cell'  => __('Cell', 'rideshare'),
            'identity_card_number'  => __('Identity Card Number', 'rideshare'),
            'identity_card_validity'    => __('Identity Card Validity', 'rideshare'),
            'street'    => __('Street', 'rideshare'),
            'zipcode'   => __('Zip Code', 'rideshare'),
            'city'  => __('City', 'rideshare'),
            'region'    => __('Region', 'rideshare'),
            'country'   => __('Country', 'rideshare'),
            'latitude'  => __('Latitude', 'rideshare'),
            'longitude' => __('Longitude', 'rideshare'),
        );

        return apply_filters('rideshare_tramp_user_labels', $labels);
    }

    /**
     * get_tramp_location_id_meta
     * 
     * Gibt die Tramp-Location-ID des Benutzers zurück
     * 
     * @param WP_User $user
     * @return mixed
     */
    static protected function get_tramp_location_id_meta($user_id)
    {
        $blog_id = static::get_blog_id();
        return get_user_meta($user_id, $blog_id . '_tramp_location_id', true);
    }

    /**
     * get_tramp_user_id_meta
     * 
     * Gibt die Tramp-User-ID des Benutzers zurück
     * 
     * @param WP_User $user
     * @return mixed
     */
    static protected function get_tramp_user_id_meta($user_id)
    {
        $blog_id = static::get_blog_id();
        $user_meta = get_user_meta($user_id, $blog_id . '_tramp_user_id', true);
        error_log(__CLASS__ . '->' . __LINE__ . '->' . __FUNCTION__ . '-> user_meta:' . print_r($user_meta, 1));
        return $user_meta;
    }


    /**
     * set_is_tramp_user_meta
     * 
     * Speichert die Information, ob der Benutzer ein Tramp-User ist
     * 
     * @param WP_User $user
     * @param bool $data
     * @return bool
     */
    static protected function set_is_tramp_user_meta($user_id, $data)
    {
        $blog_id = static::get_blog_id();
        return update_user_meta($user_id, $blog_id . '_is_tramp_user', $data);
    }

    /**
     * set_tramp_location_id_meta
     * 
     * Speichert die Tramp-Location-ID des Benutzers
     * 
     * @param WP_User $user
     * @param mixed $data
     * @return bool
     */
    static protected function set_tramp_location_id_meta($user_id, $data)
    {
        $blog_id = static::get_blog_id();
        return update_user_meta($user_id, $blog_id . '_tramp_location_id', $data);
    }

    /**
     * set_tramp_user_id_meta
     * 
     * Speichert die Tramp-User-ID des Benutzers
     * 
     * @param WP_User $user
     * @param mixed $data
     * @return bool
     */
    static protected function set_tramp_user_id_meta($user_id, $data)
    {
        $blog_id = static::get_blog_id();
        return update_user_meta($user_id, $blog_id . '_tramp_user_id', $data);
    }

    /*
    *   PUBLIC METHODS
    */
    /**
     * add_admin_tab
     * 
     * @since 1.0.39
     */
    public function add_admin_tab($tab_name, $tab_content, $tab_slug = null)
    {
        $tab_slug = $tab_slug ? strtolower(Settings::PLUGIN_NAME) . '_' . $tab_slug : strtolower(Settings::PLUGIN_NAME . '_' . preg_replace('/\W/', '_', $tab_name));
        static::$admin_page_tabs[$tab_slug]   = ['tab_name' => $tab_name, 'tab_content' => $tab_content];
    }


    /**
     * add_page
     * 
     * Admin-Menu erstellen
     * @since 1.0.39
     */
    public function add_page($tab_name, $tab)
    {
        $method         = array($tab, 'get_slug');
        $tab_slug       = call_user_func($method);
        $tab_content    = call_user_func(array($tab, 'get_view'));

        static::add_admin_tab($tab_name, $tab_content, $tab_slug);
    }


    /**
     * admin_menu
     * 
     * Admin-Menu erstellen
     * @since 1.0.39
     */
    static public function admin_menu()
    {
        $page_slug      = Settings::PLUGIN_NAME . '_' . self::ADMIN_PAGE_SLUG;

        static::$admin_hook_suffix = add_menu_page(
            __('Ridings Admin', 'rideshare'),
            __('Ridings Admin', 'rideshare'),
            'manage_options',
            $page_slug,
            [__CLASS__, 'display_admin_page'],
            'dashicons-schedule',
            30
        );

        error_log(__CLASS__ . '->' . __LINE__ . '->' . $page_slug . '->' . static::$admin_hook_suffix);
        // Bezeichnung des ersten Eintrages ändern (erster Eintrag verweist ebenfalls auf Hauptseite)
        $sub_hook_suffix = add_submenu_page(static::$admin_hook_suffix, __('Settings', 'rideshare'), __('Einstellungen', 'rideshare'), 'manage_options', $page_slug);

        foreach (static::$sub_pages as $sub_page) {
            error_log(__CLASS__ . '->' . __LINE__ . '->' . $sub_page);
            $method = array(__NAMESPACE__ . '\\' . $sub_page, 'admin_menu');
            if (is_callable($method)) {
                call_user_func($method, $page_slug);
            }
        }

        // Styles und Scripte nur bei Anzeige der Backend-Seite laden
        // add_action('admin_print_styles-' . static::$admin_hook_suffix, array(__CLASS__, 'enqueue_scripts'));

        // $loader->add_action('woocommerce_process_product_meta', $this, 'save_custom_meta_fields', 1);
        return null;
    }


    /**
     * display
     * 
     * Admin-Menu erstellen
     * @since 1.0.39
     */
    static public function display()
    {
        $tabs = [];
        $containers = [];
        $active = 'active';

        $tab_html = implode('', array(
            '<li ',
            'class="%1$s" ',
            'aria-controls="%2$s_content" ',
            'role="tab" ',
            'id="%2$s_menu_item"',
            'aria-selected="%3$s"',
            'data-store="%4$d"',
            '><a>%5$s</a></li>'
        ));
        $tab_panel_html = implode('', array(
            '<div ',
            'class="%1$s" ',
            'role="tabpanel" ',
            'id="%2$s_content"',
            '%3$s', // hidden
            '>%4$s</div>'
        ));
        $slug = 'start';
        $content = '';
        if (file_exists(Settings::PLUGIN_DIR_PATH . '/admin/templates/admin-start-template.php')) {
            ob_start();
            include_once Settings::PLUGIN_DIR_PATH . '/admin/templates/admin-start-template.php';
            $content = ob_get_clean();
        }
        $tabs[] = sprintf(
            $tab_html,
            $active,
            $slug,
            'active' === $active ? 'true' : 'false',
            0,
            __('Start', 'rideshare')
        );
        $containers[] = sprintf(
            $tab_panel_html,
            $active,
            $slug,
            'active' !== $active ? 'hidden' : '',
            $content
        );
        $active = '';
        foreach (static::$admin_page_tabs as $slug => $content) {
            $tabs[] = sprintf(
                $tab_html,
                $active,
                $slug,
                'active' === $active ? 'true' : 'false',
                1,
                $content['tab_name']
            );
            $containers[] = sprintf(
                $tab_panel_html,
                $active,
                $slug,
                'active' !== $active ? 'hidden' : '',
                $content['tab_content']
            );
        }

        $template_file  = implode(DIRECTORY_SEPARATOR, array(
            Settings::PLUGIN_DIR_PATH,
            'admin',
            'templates',
            'admin-template.php'
        ));

        $plugin_prefix  = Settings::PLUGIN_NAME;
        if (file_exists($template_file)) {
            include_once $template_file;
        } else {
            echo sprintf(
                '<div><ul>%1$s</ul><div>%2$s</div></div>',
                implode('', $tabs),
                implode('', $containers)
            );
        }
    }


    /**
     * display_admin_page
     * 
     * Seite zum Admin-Menu-Punkt anzeigen
     * 
     * @since 1.0.39
     */
    static public function display_admin_page()
    {

        foreach (static::$tabs as $entry) {

            error_log(__CLASS__ . '->' . __FUNCTION__ . '->' . __LINE__ . '-> DISPLAY_ADMIN_PAGE' . __NAMESPACE__ . '\\' . $entry);
            $method = array(__NAMESPACE__ . '\\' . $entry, 'get_title');

            if (is_callable($method)) {
                static::add_page(call_user_func($method), __NAMESPACE__ . '\\' . $entry);
            }
        }
        static::enqueue_scripts();
        static::display();
    }


    /**
     * is_allowed
     * 
     * checks, if the requested method could be called via ajax
     * 
     * @param string
     * @since   1.0.0
     */
    static function is_allowed(string $name)
    {
        return in_array($name, static::AJAX_METHODS);
    }


    /**
     * init
     * 
     * Filter und Actions der Klasse zufügen
     * @since 1.0
     */
    static public function init()
    {
        $page_slug      = Settings::PLUGIN_NAME . '_' . self::ADMIN_PAGE_SLUG;
        // error_log(__CLASS__ . '->' . __FUNCTION__ . '->' . __LINE__ . '-> INIT Backend');

        add_action('admin_menu', array(__CLASS__, 'admin_menu'));

        if (isset($_GET['page']) && $page_slug  === $_GET['page']) {
            // error_log(__CLASS__ . '->' . __FUNCTION__ . '->' . __LINE__ . '-> INIT tabS');
            foreach (static::$tabs as $entry) {
                error_log(__CLASS__ . '->' . __LINE__ . '->' . $entry);
                $method = array(__NAMESPACE__ . '\\' . $entry, 'init');
                if (is_callable($method)) {
                    call_user_func($method);
                }

                $method = array(__NAMESPACE__ . '\\' . $entry, 'enqueue_scripts');
                if (is_callable($method)) {
                    // error_log(__CLASS__ . '->' . __FUNCTION__ . '->' . __LINE__ . '->IS_SCRIPT:' . $entry . '->' . 'admin_print_styles-' . static::$admin_hook_suffix);
                    // $loader->add_action('admin_print_styles-'.static::$admin_hook_suffix, $entry, 'enqueue_scripts');
                    add_action('admin_enqueue_scripts', $method);
                    // $entry::enqueue_scripts();
                }

                $method = array(__NAMESPACE__ . '\\' . $entry, 'enqueue_styles');
                if (is_callable($method)) {
                    // error_log(__CLASS__ . '->' . __FUNCTION__ . '->' . __LINE__ . '->IS_SCRIPT:' . $entry . '->' . 'admin_print_styles-' . static::$admin_hook_suffix);
                    // $loader->add_action('admin_print_styles-'.static::$admin_hook_suffix, $entry, 'enqueue_scripts');
                    add_action('admin_enqueue_scripts', $method);
                    // $entry::enqueue_scripts();
                }
            }
        } elseif (!wp_doing_ajax()) {
            foreach (static::$sub_pages as $sub_page) {
                error_log(__CLASS__ . '->' . __LINE__ . '->' . $sub_page);
                $method = array(__NAMESPACE__ . '\\' . $sub_page, 'admin_init');
                if (is_callable($method)) {
                    add_action('admin_init', $method);
                    // add_action('rest_api_init', $method);
                }
            }
        }
        return null;
    }


    /**
     * init_json
     * 
     * initialize ajax 
     *
     * @since    1.0.0
     */


    static function init_json()
    {
        $caller = debug_backtrace()[2]['function'];
        switch ($caller) {
            case 'define_admin_hooks': {
                    add_action('wp_ajax_' . static::TARGET, [__CLASS__, 'router']);
                    break;
                }
            case 'define_public_hooks': {
                    // self::$logger->log('->wp_ajax_nopriv_' . static::TARGET . '_check' . is_callable([__CLASS__, 'check']));
                    add_action('wp_ajax_nopriv_' . static::TARGET, [__CLASS__, 'router']);
                    break;
                }
            default: {
                    add_action('wp_ajax_' . static::TARGET, [__CLASS__, 'router']);
                    add_action('wp_ajax_nopriv_' . static::TARGET, [__CLASS__, 'router']);
                }
        }
    }


    /**
     * router
     *
     * @since    1.0.0
     */
    static function router()
    {
        $request    = Request_Singleton::get_instance();

        $target = $request->get('target', 'alphanum');
        $class  = $request->get('class', 'alphanum');

        $class    = __NAMESPACE__ . '\\' . $class;

        $current_method = array($class, 'is_allowed');
        error_log(__CLASS__ . '->' . __LINE__ . '->' . $class . '-> is_allowed:' . is_callable($current_method) . '->' . $target);

        if (is_callable($current_method) && call_user_func($current_method, $target)) {
            $current_method = array($class, $target);

            if (!is_callable($current_method)) {
                $instance = new $class();
                $current_method = array($instance, $target);
            }
        }

        if (!is_callable($current_method)) {
            wp_send_json(
                array(
                    'message' => sprintf(__('the method "%1$s" of the class "%2$s" doesn\'t exist.', 'rideshare'), $target, __CLASS__)
                ),
                404
            );
        }

        $result     = call_user_func($current_method, $request);
        // $result     = array('handler' => $handler, 'method' => $method, 'request' => $request);

        wp_send_json(
            $result
        );
    }


    /**
     * save_tramp_user_data
     * 
     * Speichert die Tramp-User-Daten des Benutzers
     * 
     * @param int $user_id
     * @since 1.0.39
     */
    static public function save_tramp_user_data($user_id)
    {
        if (!current_user_can('edit_user', $user_id))
            return false;

        $is_tramp_user =  $sanitized  = 'on' === $_POST['is_tramp_user'];
        if ($is_tramp_user) {
            $location_columns = $_POST['tramp_location'];
            $user_columns = $_POST['tramp_user'];

            $missing_location_columns = Location_Controller::check($location_columns);
            $missing_user_columns = User_Controller::check($user_columns);

            $missings = array_merge($missing_location_columns, $missing_user_columns);

            if (!count($missings)) {
                if (!isset($location_columns['id']) || empty($location_columns['id'])) {
                    $tramp_location_data    = Location_Controller::create($location_columns);
                    $tramp_location_id      = $tramp_location_data[Location_Controller::get_primary_key()];
                    static::set_tramp_location_id_meta($user_id, $tramp_location_id);
                } else {
                    $location_columns = Location_Controller::update($location_columns);
                    $tramp_location_id = $location_columns['id'];
                }

                $user_columns['location_id']    = $tramp_location_id;
                if (!isset($user_columns['id']) || empty($user_columns['id'])) {
                    $tramp_user_data    = User_Controller::create($user_columns);
                    $tramp_user_id      = $tramp_user_data[User_Controller::get_primary_key()];
                    static::set_tramp_user_id_meta($user_id, $tramp_user_id);
                } else {
                    $user_columns = User_Controller::update($user_columns);
                    $tramp_user_id = $user_columns['id'];
                }
            }
            error_log(__CLASS__ . '->' . __LINE__ . '->' . print_r($missings, 1));
        }

        $result = static::set_is_tramp_user_meta($user_id, $sanitized);
    }



    /**
     * show_tramp_user_data
     * 
     * Zeigt die Tramp-User-Daten des Benutzers an
     * 
     * @param WP_User $user
     * @return void
     */
    static public function show_tramp_user_data($user)
    {
        error_log(__CLASS__ . '->' . __FUNCTION__ . '->' . __LINE__ . '-> SHOW_TRAMP_USER_DATA');
        $is_tramp_user = static::get_is_tramp_user_meta($user->ID);
        if ($is_tramp_user) {
            $tramp_location_id  = static::get_tramp_location_id_meta($user->ID);
            $tramp_user_id      = static::get_tramp_user_id_meta($user->ID);

            if ($tramp_user_id ?? null) {
                $user_columns = User_Controller::read($tramp_user_id);
                if(array_is_list($user_columns)) {
                    $user_columns = array_shift($user_columns);
                }
            } else {
                $user_columns = User_Controller::get_columns();
            }
            
            if ($tramp_location_id ?? null) {
                $location_columns = Location_Controller::read($tramp_location_id);
                if(array_is_list($location_columns)) {
                    $location_columns = array_shift($location_columns);
                }
            } else {
                $location_columns = Location_Controller::get_columns();
            }
            error_log(__CLASS__ . '->' . __LINE__ . '->' . $tramp_user_id . ':' . print_r($user_columns, 1));
            $user_columns['email']  = $user_columns['email'] ? $user_columns['email'] : $user->user_email;
        }
        $labels = static::get_labels();
        $input_types    = array(
            'givenname' => 'text',
            'familyname' => 'text',
            'birthday' => 'date',
            'email' => 'email',
            'phone' => 'tel',
            'cell' => 'tel',
            'identity_card_number' => 'text',
            'identity_card_validity' => 'date',
            'street' => 'text',
            'zipcode' => 'text',
            'city' => 'text',
            'region' => 'text',
            'country' => 'text',
            // Geodata
            'latitude' => 'number',
            'longitude' => 'number'
        );
        include_once Settings::PLUGIN_DIR_PATH     . implode(DIRECTORY_SEPARATOR, array('includes', 'admin', 'templates', 'tramp-user-settings.php'));
    }

    /**
     * get_template
     *
     * @since    1.0.0
     */
    static public function get_template($request)
    {
        $controller = __NAMESPACE__ . '\\' . ucfirst($request->get('table', 'alphanum')) . '_Controller';
        $list_table = __NAMESPACE__ . '\\Ridings_' . ucfirst($request->get('table', 'alphanum')) . '_WP_List_Table';
        $html       = '';
        $list_table_instance = new $list_table();
        if ($list_table_instance instanceof WP_List_Table) {
            $list_table_instance->prepare_items();
            ob_start();
            $list_table_instance->display();
            $html       = ob_get_clean();
        }

        $get_columns_method = array($controller, 'get_columns');
        $get_primary_key_method = array($controller, 'get_primary_key');

        if (is_callable($get_columns_method)) {
            $columns = call_user_func($get_columns_method);
        }
        if (is_callable($get_primary_key_method)) {
            $primary = call_user_func($get_primary_key_method);
        }


        return (array(
            'columns' => $columns,
            'primary' => $primary,
            'request' => $request,
            'html' => $html,
            'class' => __CLASS__,
            'nonce' => is_callable($get_columns_method),
        ));
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    static public function enqueue_scripts()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        // error_log(__CLASS__ . '->' . __FUNCTION__ . '->' . __LINE__ . '-> enqueue_scripts' );

        $css_path   = Settings::PLUGIN_DIR_PATH     . implode(DIRECTORY_SEPARATOR, array('admin', 'assets', 'css', 'admin.css'));

        if (file_exists($css_path)) {
            $css_url  = Settings::PLUGIN_URL . implode(DIRECTORY_SEPARATOR, array('admin', 'assets', 'css', 'admin.css'));
            wp_enqueue_style(Settings::PLUGIN_NAME, $css_url, array(), Settings::PLUGIN_VERSION, 'all');
        }

        $js_path   = Settings::PLUGIN_DIR_PATH . implode(DIRECTORY_SEPARATOR, array('admin', 'assets', 'js', 'admin.js'));
        if (file_exists($js_path)) {
            $js_handle  = Settings::PLUGIN_NAME . '_admin';
            $js_url  = Settings::PLUGIN_URL . implode(DIRECTORY_SEPARATOR, array('admin', 'assets', 'js', 'admin.js'));
            wp_enqueue_script($js_handle, $js_url, array('jquery'), Settings::PLUGIN_VERSION, false);
            wp_localize_script($js_handle, $js_handle . '_data', array(
                'path'      => Settings::PLUGIN_URL . 'admin/assets/js/tabs/',
                'prefix'    => strtolower(Settings::PLUGIN_NAME),
                'action'    => 'rideshare-admin-router',
                'target'    => 'get_template',
                'ajaxurl'   => admin_url('admin-ajax.php'),
            ));
        }
    }
}
