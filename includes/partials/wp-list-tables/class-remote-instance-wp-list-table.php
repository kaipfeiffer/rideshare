<?php

namespace KaiPfeiffer\Rideshare;

if (!defined('ABSPATH')) {
    exit;
}

class Remote_Instance_WP_List_Table extends WP_List_Table_Abstract
{
    function column_title($item)
    {
        $name = ($item['title'] ?? '') ?: ($item['base_url'] ?? '');
        $delete_url = wp_nonce_url(
            add_query_arg(
                array(
                    'page' => $_REQUEST['page'],
                    'action' => 'delete',
                    'id' => $item[$this->controller::get_primary_key()],
                ),
                admin_url('admin.php')
            ),
            $this->nonce,
            $this->nonce_field
        );
        $actions = array(
            'edit' => sprintf('<a href="?page=%1$s&amp;action=%2$s&amp;id=%3$d" aria-label="%5$s %4$s">%5$s</a>', $_REQUEST['page'], 'edit', $item[$this->controller::get_primary_key()], $name, __('Edit', 'rideshare')),
            'delete' => sprintf('<a href="%1$s" aria-label="%3$s %2$s">%3$s</a>', esc_url($delete_url), esc_attr($name), __('Delete', 'rideshare')),
        );

        return sprintf(
            '%1$s %2$s',
            esc_html($name),
            $this->row_actions($actions)
        );
    }

    function column_base_url($item)
    {
        $base_url = $item['base_url'] ?? '';

        if (!$base_url) {
            return '';
        }

        return sprintf(
            '<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
            esc_url($base_url),
            esc_html($base_url)
        );
    }

    function column_status($item)
    {
        return esc_html(Remote_Instance_Controller::get_status_label($item['status'] ?? ''));
    }

    function column_mode($item)
    {
        return esc_html(Remote_Instance_Controller::get_mode_label($item['mode'] ?? ''));
    }

    function get_columns()
    {
        $columns = parent::get_columns();
        unset($columns['shared_secret']);

        $columns['last_seen'] = __('Last seen', 'rideshare');

        return $columns;
    }
}
