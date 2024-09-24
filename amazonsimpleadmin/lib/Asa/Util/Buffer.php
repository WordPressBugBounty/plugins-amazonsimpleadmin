<?php
/**
 * AmazonSimpleAffiliate
 * For more information see http://www.wp-amazon-plugin.com/
 *
 *
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Buffer.php 1838088 2018-03-11 15:25:25Z worschtebrot $
 */
class Asa_Util_Buffer
{
    /**
     * @var array
     */
    protected static $_buffer = array();

    /**
     * @var bool
     */
    public static $debug = false;


    /**
     * @param $token
     * @param string $ns
     * @return bool
     */
    public static function exists($token, $ns = 'default')
    {
        $result = isset(self::$_buffer[$ns]) && array_key_exists($token, self::$_buffer[$ns]);
        if (self::$debug) {
            trigger_error(sprintf('%s: $token: %s, $ns: %s, $result: %s', __METHOD__, $token, $ns, ($result ? 'true' : 'false')), E_USER_NOTICE);
        }
        return $result;
    }

    /**
     * @param $token
     * @param string $ns
     * @return mixed|null
     */
    public static function get($token, $ns = 'default')
    {
        if (self::exists($token, $ns)) {
            if (self::$debug) {
                trigger_error(sprintf('%s: $token: %s, $ns: %s', __METHOD__, $token, $ns), E_USER_NOTICE);
            }
            return self::$_buffer[$ns][$token];
        }
        return null;
    }

    /**
     * @param $token
     * @param $data
     * @param string $ns
     */
    public static function set($token, $data, $ns = 'default')
    {
        if (!isset(self::$_buffer[$ns])) {
            self::$_buffer[$ns] = array();
        }
        if (self::$debug) {
            trigger_error(sprintf('%s: $token: %s, $ns: %s', __METHOD__, $token, $ns), E_USER_NOTICE);
        }
        self::$_buffer[$ns][$token] = $data;
    }
}
