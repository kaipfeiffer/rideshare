<?php

namespace KaiPfeiffer\Rideshare;

if (!defined('WPINC')) {
    die;
}

/**
 * controller for locations
 *
 * @author  Kai Pfeiffer <kp@loworx.com>
 * @package rideshare
 * @since   1.0.0 
 */

class User_Controller extends Controller_Abstract
{


    /** 
     * NONCE 
     * 
     * string to create an unique nonce
     */
    const NONCE = 'loworx_user_controller_nonce';

    /**
     * $tramp_class
     * 
     * class for tramp locations
     * 
     * @var string
     */
    static protected $model_class = null;



    static function get_column_labels()
    {
        $columns = array(
            'title' => array(
                'type' => 'text',
                'label' => __('Title', 'rideshare')
            ),
            'familyname' => array(
                'type' => 'text',
                'label'    => __('Family name', 'rideshare')
            ),
            'givenname' => array(
                'type' => 'text',
                'label' => __('Given name', 'rideshare')
            ),
            'birthday' => array(
                'type' => 'date',
                'label'  => __('Birthday', 'rideshare')
            ),
            'email' => array(
                'type' => 'email',
                'label' => __('E-Mail', 'rideshare')
            ),
            'phone' => array(
                'type' => 'tel',
                'label' => __('Phone', 'rideshare')
            ),
            'cell' => array(
                'type' => 'tel',
                'label'  => __('Cell', 'rideshare')
            ),
            // 'id' => 'ID',
            'identity_card_number' => array(
                'type' => 'text',
                'label'  => __('Identity Card Number', 'rideshare')
            ),
            'identity_card_validity' => array(
                'type' => 'date',
                'label'    => __('Identity Card Validity', 'rideshare')
            ),
            'location_id' => array(
                'type' => 'autocomplete',
                'label'  => __('Location', 'rideshare')
            ),
        );
        return $columns;
    }

    static function get_title($row)
    {
        return sprintf('%s %s',
            $row['givenname'] ?? '',
            $row['familyname'] ?? ''
        );
    }
}
