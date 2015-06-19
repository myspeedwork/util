<?php

/**
 * This file is part of the Speedwork framework.
 *
 * @link http://github.com/speedwork
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Speedwork\Util;

/**
 * @author sankar <sankar.suda@gmail.com>
 */
class Router
{
    /**
     * rewrite engines array.
     *
     * @var array
     */
    private static $_rewrite = [];

    /**
     * generate url link from url.
     *
     * @param string $url     full url without domain
     * @param bool   $amp     convert & to &amp;
     * @param bool   $ssl     is sll url
     * @param bool   $rewrite enable rewrite if avalable
     *
     * @return string return the complete with without domain
     */
    public static function link($url, $amp = true, $ssl = false, $rewrite = true)
    {
        if ($rewrite) {
            foreach (static::$_rewrite as $re) {
                $url = $re->rewrite($url);
            }
        } else {
            $url = static::fixLink($url);
        }

        if (!preg_match('/(https?):\/\//', $url)
            && substr($url, 0, 2) != '//') {
            $url = _URL.$url;
        }

        if ($amp) {
            $url = Utility::specialchars($url);
        }

        if ($ssl) {
            $url = str_replace('http://', 'https://', $url);
        }

        return $url;
    }

    public static function fix($url)
    {
        if (!preg_match('/(https?):\/\//', $url)) {
            if (substr($url, 0, 2) != '//') {
                $url = 'http://'.$url;
            } else {
                $url = 'http:'.$url;
            }
        }

        return $url;
    }

    public static function fixLink($url)
    {
        $url = trim($url);
        $url = preg_replace('/\s{2,}/', ' ', $url);
        // Replace spaces
        $url = preg_replace('/\s/u', '%20', $url);
        $url = str_replace('&amp;', '&', $url);

        //replace com_
        $url = str_replace('com_', '', $url);

        if (!preg_match('/(https?):\/\//', $url) && substr($url, 0, 2) != '//') {
            if (substr($url, 0, 5) != 'index' && substr($url, 0, 6) != '/index') {
                $split   = explode('&', $url, 2);
                $details = explode('/', $split[0]);
                $url     = 'index.php?option='.$details[0];
                if ($details[1]) {
                    $url = $url.'&view='.$details[1];
                }
                if ($split[1]) {
                    $url = $url.'&'.$split[1];
                }
            }
        }

        return $url;
    }

    /**
     * add rewrite methods to process url.
     *
     * @param null $rewrite
     */
    public static function addRewrite($rewrite)
    {
        static::$_rewrite[] = $rewrite;
    }
}