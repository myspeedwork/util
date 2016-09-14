<?php

/*
 * This file is part of the Speedwork package.
 *
 * (c) Sankar <sankar.suda@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Speedwork\Util;

use Exception;
use Speedwork\Core\Traits\Macroable;

/**
 * @author sankar <sankar.suda@gmail.com>
 */
class Utility
{
    use Macroable;

    /**
     * generate arrribue from key value array.
     *
     * @param array $data [description]
     *
     * @return string [description]
     */
    public static function parseAttributes($data)
    {
        if (!$data) {
            return;
        }

        if (!is_array($data)) {
            return;
        }

        foreach ($data as $key => $value) {
            $attr .= $key.'="'.$value.'" ';
        }

        return $attr;
    }

    public static function currentUrl()
    {
        $pageURL = 'http';
        if (env('HTTPS') == 'on') {
            $pageURL .= 's';
        }
        $pageURL .= '://';
        if (env('SERVER_PORT') != '80') {
            $pageURL .= env('HTTP_HOST').':'.env('SERVER_PORT').env('REQUEST_URI');
        } else {
            $pageURL .= env('HTTP_HOST').env('REQUEST_URI');
        }

        return $pageURL;
    }

    public static function download($fullPath)
    {
        if ($fd = fopen($fullPath, 'r')) {
            $fsize      = filesize($fullPath);
            $path_parts = pathinfo($fullPath);
            $ext        = strtolower($path_parts['extension']);
            switch ($ext) {
            case 'pdf':
                header('Content-type: application/pdf'); // add here more headers for diff. extensions
                header('Content-Disposition: attachment; filename="'.$path_parts['basename'].'"'); // use 'attachment' to force a download
                break;
            default:
                header('Content-type: application/octet-stream');
                header('Content-Disposition: filename="'.$path_parts['basename'].'"');
                break;
            }
            header("Content-length: $fsize");
            header('Cache-control: private'); //use this to open files directly
            while (!feof($fd)) {
                $buffer = fread($fd, 2048);
                echo $buffer;
            }
        }
        fclose($fd);
    }

    /**
     * Fetch the IP address of the current visitor.
     *
     * @return string The IP address
     */
    public static function ip()
    {
        $ip = env('REMOTE_ADDR');

        if (empty($ip) && env('HTTP_X_FORWARDED_FOR')) {
            if (preg_match_all("#[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}#s", env('HTTP_X_FORWARDED_FOR'), $addresses)) {
                foreach ($addresses[0] as $address) {
                    if (!preg_match("#^(10|172\.16|192\.168)\.#", $address)) {
                        $ip = $address;
                        break;
                    }
                }
            }
        }

        if (empty($ip)) {
            if (env('HTTP_CLIENT_IP')) {
                $ip = env('HTTP_CLIENT_IP');
            } elseif (env('REMOTE_ADDR')) {
                $ip = env('REMOTE_ADDR');
            }
        }
        $ip = preg_replace('#([^.0-9 ]*)#', '', $ip);

        return $ip;
    }

    public static function parseQuery($var)
    {
        $var = parse_url($var, PHP_URL_QUERY);
        $var = html_entity_decode($var);
        $var = explode('&', $var);
        $arr = [];

        foreach ($var as $val) {
            $x          = explode('=', $val);
            $arr[$x[0]] = $x[1];
        }
        unset($val, $x, $var);

        return $arr;
    }

    public static function call($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);

        return $output;
    }

    /**
     * Method to extract key/value pairs out of a string with xml style attributes.
     *
     * @param string $string String containing xml style attributes
     *
     * @return array Key/Value pairs for the attributes
     */
    public static function parseXmlAttributes($string)
    {
        //Initialize variables
        $attr     = [];
        $retarray = [];

        // Lets grab all the key/value pairs using a regular expression
        preg_match_all('/([\w:-]+)[\s]?=[\s]?"([^"]*)"/i', $string, $attr);

        if (is_array($attr)) {
            $numPairs = count($attr[1]);
            for ($i = 0; $i < $numPairs; ++$i) {
                $retarray[$attr[1][$i]] = $attr[2][$i];
            }
        }

        return $retarray;
    }

    public static function ipMatch($allowed = [])
    {
        $address = self::ip();
        foreach ($allowed as $ip) {
            $ipregex = str_replace('.', "\.", $ip);
            $ipregex = str_replace('*', '.*', $ipregex);

            //extract the last part of valid IP and extracts the pattern
            $lastpart    = substr($ip, (strrpos($ip, '.') + 1));
            $rangeFormat = '';
            if (false !== strpos($lastpart, '-')) {
                $rangeFormat = '-';
            } elseif (false !== strpos($lastpart, '/')) {
                $rangeFormat = '/';
            }

            //Modify the last part of expression depending on pattern/range
            //ex: 20/30/40 -> (20|30|40)
            //ex: 20-25 -> (20|21|22|23|24|25)
            if ($rangeFormat) {
                $range = explode($rangeFormat, $lastpart);
                if ($rangeFormat == '-') {
                    $range = range($range[0], $range[1]);
                }
                $ipregex = str_replace($lastpart, '('.implode('|', $range).')', $ipregex);
            }
            unset($range, $rangeFormat, $lastpart);

            if (preg_match('/^'.$ipregex.'/', $address)) {
                return true;
            }
        }

        return false;
    }

    public static function strtotime($time, $date = false, $format = 'Y-m-d')
    {
        if (!is_numeric($time)) {
            $parts = explode('/', $time);

            if ($parts[0] && strlen($parts[0]) != 4) {
                $time = str_replace('/', '-', trim($time));
            }

            $convert = config('app.datesettings.convert');

            if ($convert) {
                $time = strtotime($time.' '.$convert);
            } else {
                $time = strtotime($time);
            }
        }

        if ($date) {
            return date($format, $time);
        }

        return $time;
    }

    /**
     * Creating between two date.
     *
     * @param string since
     * @param string until
     * @param string step
     * @param string date format
     *
     * @return array
     *
     * @author sankara rao <sankar.suda@gmail.com>
     */
    public static function dateRange($first, $last, $step = '+1 day', $format = 'd/m/Y')
    {
        $dates   = [];
        $current = self::strtotime($first);
        $last    = self::strtotime($last);

        while ($current <= $last) {
            $dates[] = date($format, $current);
            $current = strtotime($step, $current);
        }

        // Fix if first day > last day
        if (date($format, $current) == date($format, $last)) {
            $dates[] = date($format, $current);
        }

        return array_unique($dates);
    }

    /**
     * Joining multiple files.
     *
     * @param array  $files  [description]
     * @param string $result [description]
     * @param bool   $delete [description]
     *
     * @return [type] [description]
     */
    public static function joinFiles(array $files, $result, $delete = false)
    {
        if (!is_array($files)) {
            throw new Exception('`$files` must be an array');
        }

        if (function_exists('exec')) {
            $cmd = 'paste -d"\n" "'.implode('" "', $files).'" > '.$result;
            $cmd = 'cat  "'.implode('" "', $files).'" > '.$result;
            exec($cmd);
            foreach ($files as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }

            return true;
        }

        $wH = fopen($result, 'a+');

        foreach ($files as $file) {
            if (!file_exists($file)) {
                continue;
            }

            $fh = fopen($file, 'r');
            while (!feof($fh)) {
                fwrite($wH, fgets($fh));
            }
            fclose($fh);
            unset($fh);
            fwrite($wH, "\n"); //usually last line doesn't have a newline

            if ($delete) {
                unlink($file);
            }
        }
        fclose($wH);
        unset($wH);

        return true;
    }
}
