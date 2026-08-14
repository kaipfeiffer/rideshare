<?php

namespace KaiPfeiffer\Rideshare;

if (!defined('WPINC')) {
    die;
}

/**
 * Registers and renders the rideshare riding widget block.
 *
 * @author  Kai Pfeiffer <kp@loworx.com>
 * @package rideshare
 * @since   1.0.0
 */
class Riding_Widget_Block
{
    static function register(): void
    {
        $block_dir = Settings::PLUGIN_DIR_PATH . 'build/rideshare';

        if (!file_exists($block_dir . '/block.json')) {
            return;
        }

        register_block_type($block_dir, array('render_callback' => array(static::class, 'render_callback')));
    }

    /**
     * Registers blocks from the metadata collection when available.
     *
     * This is currently unused, but kept out of the main plugin file for the
     * next step to manifest-based block registration.
     */
    static function register_from_manifest(): void
    {
        $build_dir = Settings::PLUGIN_DIR_PATH . 'build';
        $manifest_file = $build_dir . '/blocks-manifest.php';

        if (!file_exists($manifest_file)) {
            return;
        }

        if (function_exists('wp_register_block_types_from_metadata_collection')) {
            wp_register_block_types_from_metadata_collection($build_dir, $manifest_file);
            return;
        }

        if (function_exists('wp_register_block_metadata_collection')) {
            wp_register_block_metadata_collection($build_dir, $manifest_file);
        }

        $manifest_data = require $manifest_file;
        foreach (array_keys($manifest_data) as $block_type) {
            register_block_type($build_dir . "/{$block_type}", array('render_callback' => array(static::class, 'render_callback')));
        }
    }

    static function render_callback($attributes, $content, $block): string
    {
        $name_parts = explode('/', $block->name ?? '/');
        $slug = end($name_parts);

        if ('rideshare' === $slug) {
            return static::render((array) $attributes);
        }

        return '';
    }

    static function render(array $attributes = array()): string
    {
        $initial_data = Riding_Controller::get_client_data();
        $riding_items = $initial_data['riding_items'];
        $can_use = $initial_data['can_create'];
        $field_id_prefix = wp_unique_id('rideshare-riding-widget-');

        ob_start();
        include Settings::PLUGIN_DIR_PATH . 'build/rideshare/render.php';
        return ob_get_clean();
    }
}
