<?php

namespace KaiPfeiffer\Rideshare;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @class JWT_Singleton
 * 
 * @author  Kai Pfeiffer <kp@loworx.com>
 * @package rideshare
 * @since   1.0.0 
 */
class Routing_Handler
{

    /** 
     * NONCE 
     * 
     * string, der zur eindeutigen Identifizierung des Nonces benötigt wird
     */
    const NONCE = 'loworx_rideshare_router_nonce';


    /** 
     * TARGET
     * 
     * Das Ajax-Ziel über das die Registrierung erfolgt
     * 
     * @const string
     */
    const TARGET    = 'rideshare-router';

    /**
     * Logger instance
     *
     * @since 1.0.0
     * @access private
     * @static
     *
     * @var Logger_Singleton
     */
    private static $logger;


    /**
     * rest
     * 
     * imitiert eine REST-Schnittstelle
     *
     * @since    1.0.0
     */
    static function router()
    {
        $request    = Request_Singleton::get_instance();

        $target     = $request->get('target', 'alphanum');

        $method     = strtolower($_SERVER['REQUEST_METHOD']);
        $current_method = null;

        /*
		 * HTTP method override for clients that can't use PUT/PATCH/DELETE. First, we check
		 * $_GET['_method']. If that is not set, we check for the HTTP_X_HTTP_METHOD_OVERRIDE
		 * header.
         * thanks to the Wordpress-Team the code in WP_REST_Server inspired the following five lines
		 */
        if (isset($_GET['_method'])) {
            $method =  sanitize_key(wp_unslash($_GET['_method']));
        } elseif (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
            $method =  sanitize_key(wp_unslash($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']));
        }



        $handler    = __NAMESPACE__ . '\\' . ucfirst(strtolower($target)) . '_Controller';

        if (is_object(static::$logger) && method_exists(static::$logger, 'log')) {
            static::$logger->log($handler);
        }
        $check_method = array($handler, 'is_allowed');
        $remote_check_method = array($handler, 'is_remote_allowed');
        $is_allowed = is_callable($check_method) && call_user_func($check_method, $method);
        $is_remote_method = is_callable($remote_check_method) && call_user_func($remote_check_method, $method);

        if ($is_remote_method) {
            $remote_authentication = Remote_Request_Authentication_Helper::authenticate_current_request($target, $method);
            if (is_wp_error($remote_authentication)) {
                wp_send_json_error(
                    array('message' => $remote_authentication->get_error_message()),
                    403
                );
            }
        }

        if ($is_allowed || $is_remote_method) {
            $current_method = array($handler, $method);
            if (!is_callable($current_method)) {
                $instance       = new $handler();
                $current_method = array($instance, $method);
                if (!is_callable($current_method)) {
                    $current_method = null;
                }
            }
        }

        if (!is_callable($current_method)) {
            wp_send_json(
                array(
                    'message' => sprintf(__('the method "%1$s" of the class "%2$s" doesn\'t exist.','rideshare'), $method, $handler)
                ),
                404
            );
        }

        $result     = call_user_func($current_method, $request->get());
        // $result     = array('handler' => $handler, 'method' => $method, 'request' => $request);

        wp_send_json(
            $result
        );
    }


    /**
     * call
     * 
     * Filter und Actions der Klasse zufügen
     *
     * @param   string  $target
     * @param   string  $method
     * @param   array   $request
     * @return  array|object|null
     * @since    1.0.0
     */


    static function call($target, $method, $request = null)
    {
        $request    = $request ? $request : Request_Singleton::get_instance();
        $handler    = __NAMESPACE__ . '\\' . ucfirst(strtolower($target)) . '_Resource';
        // class_alias($handler,$handler.'_');

        error_log(__CLASS__ . '->' . __LINE__ . '->REGEX:' . $target . '->' . (strtolower($target) === $target) . '-' . "\n");
        // Autoloader::autoload($handler);

        if (!is_callable(array($handler, $method))) {
            wp_send_json(
                array(
                    'message' => sprintf('Die Methode "%1$s" der Klasse "%2$s" existiert nicht', $method, $handler)
                ),
                404
            );
        }

        $result     = call_user_func(array($handler, $method), $request);

        return $result;
    }


    /**
     * init_json
     * 
     * initialize ajax 
     *
     * @since    1.0.0
     */


    static function init_json($logger)
    {
        self::$logger   = $logger;
        $caller = debug_backtrace()[2]['function'];
        switch ($caller) {
            case 'define_admin_hooks': {
                    // self::$logger->log('-> Admin');
                    add_action('wp_ajax_' . static::TARGET, [__CLASS__, 'router']);
                    break;
                }
            case 'define_public_hooks': {
                    add_action('wp_ajax_nopriv_' . static::TARGET, [__CLASS__, 'router']);
                    break;
                }
            default: {
                    add_action('wp_ajax_' . static::TARGET, [__CLASS__, 'router']);
                    add_action('wp_ajax_nopriv_' . static::TARGET, [__CLASS__, 'router']);
                }
        }
    }
}
