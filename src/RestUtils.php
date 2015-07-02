<?php

/**
 * This file is part of the Speedwork package.
 *
 * (c) 2s Technologies <info@2stechno.com>
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
    public static function sendResponse(&$body = '', $content_type = 'text/html', $name = '')
    {
        // set the content type
        header('Content-type: '.$content_type.'; charset=utf8');

        if ($content_type == 'application/json') {
            header('Content-Disposition: attachment; filename="'.$name.'.json"');
        }

        echo $body;
    }

    public static function getStatusCodeMessage()
    {
        // these could be stored in a .ini file and loaded
        // via parse_ini_file()... however, this will suffice
        // for an example
        $codes = [
            100 => 'Continue',
            101 => 'Switching Protocols',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            306 => '(Unused)',
            307 => 'Temporary Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
        ];

        return $codes;
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
            case 'application/x-www-form-urlencoded':
                parse_str($body, $body);
                break;
        }

        if (is_array($body)) {
            $data = array_merge_recursive($data, $body);
        }

        unset($body);

        return $data;
    }
}
