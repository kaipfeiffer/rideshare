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
    const UUID_META_KEY = 'rideshare_user_uuid';


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

    public static function create($data)
    {
        if (empty($data['uuid'])) {
            $data['uuid'] = User_Model::create_uuid();
        }

        return parent::create($data);
    }

    static function get_uuid_meta_key(): string
    {
        return static::UUID_META_KEY;
    }

    static function ensure_wordpress_user_uuid(int $user_id): string
    {
        if (!$user_id) {
            return '';
        }

        $uuid = (string) get_user_meta($user_id, static::get_uuid_meta_key(), true);
        if ($uuid) {
            return $uuid;
        }

        $uuid = User_Model::create_uuid();
        update_user_meta($user_id, static::get_uuid_meta_key(), $uuid);

        return $uuid;
    }

    static function get_current_user_uuid(): string
    {
        return static::ensure_wordpress_user_uuid(get_current_user_id());
    }

    static function ensure_rideshare_user_uuids(): void
    {
        $users = get_users(array(
            'role__in' => array('rideshare_partner', 'rideshare_user'),
            'fields' => 'ID',
        ));

        foreach ($users as $user_id) {
            static::ensure_wordpress_user_uuid(intval($user_id));
        }
    }



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
