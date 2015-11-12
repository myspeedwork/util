<?php

/**
 * This file is part of the Speedwork package.
 *
 * @link http://github.com/speedwork
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Speedwork\Util;

/**
 * @author Sankar Suda <sankar.suda@gmail.com>
 */
class RestUtils
{
    public static function sendResponse(&$body = '', $content_type = 'text/html')
    {
        // set the content type
        header('Content-type: '.$content_type.'; charset=utf8');

        return $body;
    }

    public static function processRequest()
    {
        $data = [];
        $data = array_merge_recursive($data, $_GET);
        $data = array_merge_recursive($data, $_POST);

        $body = file_get_contents('php://input');
        if ($body) {
            $content_type = false;
            if (isset($_SERVER['CONTENT_TYPE'])) {
                $content_type = strtolower($_SERVER['CONTENT_TYPE']);
                $content_type = explode(';', $content_type);
                $content_type = $content_type[0];
            }
        }

        switch ($content_type) {
            case 'application/json':
                $body = json_decode($body, true);
                break;
        }

        if (is_array($body)) {
            $data = array_merge_recursive($data, $body);
        }

        unset($body);

        return $data;
    }
}
