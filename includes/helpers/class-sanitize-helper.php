<?php

namespace KaiPfeiffer\Rideshare;

if (!defined('WPINC')) {
    die;
}

/**
 * Class for sanitization
 *
 * @author  Kai Pfeiffer <kp@loworx.com>
 * @package rideshare
 * @since   1.0.0 
 */

class Sanitize{

    /**
     * all permitted chars for hexadecimals
     */
    const REG_HEXADECIMAL   = '/[^0-9a-fA-F]/';


    /**
     * PUBLIC METHODS
     */


    /**
     * @function _decimal
     * 
     * sanitizes an decimal 
     * wrapper for function _float()
     * 
     * @param   float|string $value
     * @return  int 
     */
    public static function _decimal($value){
        return static::_float($value);
    }

    /**
     * @function _email
     * 
     * sanitizes an email-address
     * 
     * @param   float|string $value
     * @return  int 
     */
    public static function _email($value){
        // TODO regular expression and email validation
        return static::_float($value);
    }

    /**
     * @function _float
     * 
     * sanitizes an float
     * 
     * @param   float|string $value
     * @return  int 
     */
    public static function _float($value){
        // TODO i16n accept country specific values
        return floatval($value);
    }

    /**
     * @function _hexadecimal
     * 
     * sanitizes an hexadecimal
     * 
     * @param   string $value
     * @return  string
     */
    public static function _hexadecimal($value){
        return preg_replace(static::REG_HEXADECIMAL,'',$value);
    }


    /**
     * @function _integer
     * 
     * sanitizes an integer
     * 
     * @param   int|string $value
     * @return  int 
     */
    public static function _integer($value){
        return intval($value);
    }

} 