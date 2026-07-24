<?php

namespace KaiPfeiffer\Rideshare;

if (!defined('ABSPATH')) {
    exit;
}

class Stop_WP_List_Table extends WP_List_Table_Abstract
{
    function column_title($item)
    {
        $name = sprintf(
            '%1$s',
            $item['title'],
        );
        $actions = array(
            'edit'      => sprintf('<a href="?page=%1$s&amp;action=%2$s&amp;id=%3$d" aria-label="%5$s %4$s">%5$s</a>', $_REQUEST['page'], 'edit', $item[$this->controller::get_primary_key()], $name, __('Edit','rideshare')),
            'delete'    => sprintf('<a href="?page=%1$s&amp;action=%2$s&amp;id=%3$d" aria-label="%5$s %4$s">%5$s</a>', $_REQUEST['page'], 'delete', $item[$this->controller::get_primary_key()], $name, __('Delete','rideshare')),
        );

        return sprintf(
            '%1$s %2$s',
            $name,
            $this->row_actions($actions)
        );
    }
}
