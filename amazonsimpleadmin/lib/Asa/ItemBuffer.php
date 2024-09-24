<?php
/**
 * AmazonSimpleAdmin (ASA1)
 * For more information see http://www.wp-amazon-plugin.com/
 *
 *
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: ItemBuffer.php 1838088 2018-03-11 15:25:25Z worschtebrot $
 */
require_once ASA_LIB_DIR . 'Asa/Util/Buffer.php';

class Asa_ItemBuffer
{
    const BUFFER_TOKEN = 'asa_item_buffer';


    /**
     * @param $asin
     * @return bool
     */
    public static function hasItem($asin)
    {
        return Asa_Util_Buffer::exists($asin, self::BUFFER_TOKEN);
    }

    /**
     * @param $asin
     * @return mixed|null
     */
    public static function getItem($asin)
    {
        return Asa_Util_Buffer::get($asin, self::BUFFER_TOKEN);
    }

    /**
     * @param $asin
     * @param $item
     */
    public static function putItem($asin, $item)
    {
        //Asa_Util_Buffer::$debug = true;
        Asa_Util_Buffer::set($asin, $item, self::BUFFER_TOKEN);
    }
}
