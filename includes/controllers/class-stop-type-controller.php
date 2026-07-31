<?php

namespace KaiPfeiffer\Rideshare;

if (!defined('WPINC')) {
    die;
}

/**
 * Controller for stop types.
 *
 * @author  Kai Pfeiffer <kp@loworx.com>
 * @package rideshare
 * @since   1.0.0
 */
class Stop_Type_Controller extends Controller_Abstract
{
    const NONCE = 'loworx_stop_type_controller_nonce';

    static protected $model_class = null;

    static public function get_stop_type_select_options(array $options, string $column_name, $form): array
    {
        $rows = Stop_Type_Model::read(null);

        foreach ($rows as $row) {
            if (!empty($row['id']) && !empty($row['title'])) {
                $options[$row['id']] = $row['title'];
            }
        }

        if (!$options) {
            foreach (Stop_Type_Model::get_default_titles() as $index => $title) {
                $options[$index + 1] = $title;
            }
        }

        return $options;
    }

    static public function get_stop_type_label($value): string
    {
        $id = intval($value);

        if (!$id) {
            return '';
        }

        $row = Stop_Type_Model::read($id);

        if (array_is_list($row)) {
            $row = array_shift($row);
        }

        return is_array($row) ? ($row['title'] ?? '') : '';
    }
}
