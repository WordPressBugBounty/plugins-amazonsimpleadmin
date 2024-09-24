<?php
/**
 * AmazonSimpleAdmin (ASA1)
 * For more information see http://www.wp-amazon-plugin.com/
 *
 *
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: CssLoading.php 1842119 2018-03-18 11:38:07Z worschtebrot $
 */
class Asa_CssLoading
{
    /**
     * @var Asa_CssLoading
     */
    protected static $_instance;

    /**
     * @return Asa_CssLoading
     */
    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * @param $style
     * @return string
     */
    public function getHtml($style)
    {
        switch ($style) {
            case 'fb_blocks':
                $output = '<div class="loadFacebookG_box"><div class="facebook_blockG blockG_1"></div><div class="facebook_blockG blockG_2"></div><div class="facebook_blockG blockG_3"></div></div>';
                break;
            case '3_circles':
                $output = '<div class="circleG_box"><div class="circleG circleG_1"></div><div class="circleG circleG_2"></div><div class="circleG circleG_3"></div></div>';
                break;
            case 'floating_bars':
                $output = '<div class="floatingBarsG"><div class="blockG rotateG_01"></div><div class="blockG rotateG_02"></div><div class="blockG rotateG_03"></div><div class="blockG rotateG_04"></div><div class="blockG rotateG_05"></div><div class="blockG rotateG_06"></div><div class="blockG rotateG_07"></div><div class="blockG rotateG_08"></div></div>';
                break;
            case 'circular':
                $output = '<div class="circularG_box"><div class="circularG circularG_1"></div><div class="circularG circularG_2"></div><div class="circularG circularG_3"></div><div class="circularG circularG_4"></div><div class="circularG circularG_5"></div><div class="circularG circularG_6"></div><div class="circularG circularG_7"></div><div class="circularG circularG_8"></div></div>';
                break;
            default:
                $output = '';
        }
        return $output;
    }

    /**
     * @param $style
     * @param bool $onlyOncePerRequest
     * @return mixed|null
     */
    public function getCss($style, $onlyOncePerRequest = true)
    {
        $result = '';

        if (!Asa_Util_Buffer::exists($style, 'loading_css')) {
            switch ($style) {
                case 'fb_blocks':
                    $output = asa_compress_css(file_get_contents(dirname(__FILE__) . '/CssLoading/fb_blocks.css'));
                    break;
                case '3_circles':
                    $output = asa_compress_css(file_get_contents(dirname(__FILE__) . '/CssLoading/3_circles.css'));
                    break;
                case 'floating_bars':
                    $output = asa_compress_css(file_get_contents(dirname(__FILE__) . '/CssLoading/floating_bars.css'));
                    break;
                case 'circular':
                    $output = asa_compress_css(file_get_contents(dirname(__FILE__) . '/CssLoading/circular.css'));
                    break;
                default:
                    $output = '';
            }
            Asa_Util_Buffer::set($style, $output, 'loading_css');
        }

        if (!Asa_Util_Buffer::exists($style, 'loading_css_request')) {
            $result = Asa_Util_Buffer::get($style, 'loading_css');
            if ($onlyOncePerRequest) {
                Asa_Util_Buffer::set($style, true, 'loading_css_request');
            }
        }

        return $result;
    }
}
