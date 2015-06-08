<?php

/**
 * This file is part of the Speedwork package.
 *
 * (c) 2s Technologies <info@2stech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Speedwork\Util;

/**
 * @author sankar <sankar.suda@gmail.com>
 */
class Utility
{
    /**
     * use this function to destroy directory and their subdirectories and data.
     **/
    public function destroyDir($dir, $virtual = false, $removeDir = true)
    {
        $ds  = DIRECTORY_SEPARATOR;
        $dir = $virtual ? realpath($dir) : $dir;
        $dir = substr($dir, -1) == $ds ? substr($dir, 0, -1) : $dir;
        if (is_dir($dir) && $handle = opendir($dir)) {
            while ($file = readdir($handle)) {
                if ($file == '.' || $file == '..') {
                    continue;
                } elseif (is_dir($dir.$ds.$file)) {
                    $this->destroyDir($dir.$ds.$file, false, true);
                } else {
                    unlink($dir.$ds.$file);
                }
            }
            closedir($handle);
            if ($removeDir) {
                rmdir($dir);
            }

            return true;
        } elseif (is_file($dir)) {
            unlink($dir);

            return true;
        } else {
            return false;
        }
    }

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

    /**
     * remove unwanted strings from string.
     *
     * @param [type] $name [description]
     *
     * @return [type] [description]
     */
    public static function clearUnWanted($name)
    {
        $unwantedChars['in'] = [',',"'",'"','...','!'];
        $name                = str_replace($unwantedChars['in'], '', $name);
        $name                = str_replace(' ', '-', $name);
        $name                = str_replace('&', '&amp;', $name);
        $name                = trim($name);
        $name                = stripslashes($name);

        return $name;
    }

    /**
     * make name process to store in folders.
     *
     * @param [type] $title   [description]
     * @param string $replace [description]
     * @param bool   $lower   [description]
     *
     * @return [type] [description]
     */
    public static function safename($title, $replace = '-', $lower = true)
    {
        // replaces every unwanted character form a string with - ;
        $arrStupid = ['feat.', 'feat', '.com', '(tm)', ' ', '*', "'s",  '"', ',', ':', ';', '@', '#', '(', ')', '?', '!', '_',
                             '$','+', '=', '|', "'", '/', '~', '`s', '`', '\\', '^', '[',']','{', '}', '<', '>', '%', '.', ];

        $title = htmlentities($title);
        $title = str_replace($arrStupid, ' ', $title);
        $title = preg_replace('/[\s\W]+/', $replace, $title);    // Strip off spaces and non-alpha-numeric

        $title = preg_replace('/\%/', ' ', $title);
        $title = preg_replace('/\@/', ' at ', $title);
        $title = preg_replace('/\&/', ' and ', $title);
        $title = preg_replace('/\s[\s]+/', '-', $title);    // Strip off multiple spaces
        $title = preg_replace('/^[\-]+/', '', $title); // Strip off the starting hyphens
        $title = preg_replace('/[\-]+$/', '', $title); // // Strip off the ending hyphens

        if ($lower) {
            $title = strtolower($title);
        }

        return trim($title);
    }

    /**
     * safe the filename with removing the extension.
     *
     * @param [type] $title [description]
     *
     * @return [type] [description]
     */
    public static function safefile($title)
    {
        $ext   = strrchr($title, '.');
        $title = rtrim($title, $ext);

        return $this->safename($title);
    }

    /**
     * helper function to validate emall address.
     *
     * @param [type] $email [description]
     *
     * @return bool [description]
     */
    public static function is_real_email_address($email)
    {
        return (bool) (preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $email));
    }

    /**
     * generate randone code based on lenght given.
     *
     * @param int $characters [description]
     *
     * @return string [description]
     */
    public function generateCode($characters)
    {
        /* list all possible characters, similar looking characters and vowels have been removed */
        $possible = '0123456789bcdfghjkmnpqrstvwxyz';
        $code     = '';
        $i        = 0;
        while ($i < $characters) {
            $code .= substr($possible, mt_rand(0, strlen($possible) - 1), 1);
            $i++;
        }

        return $code;
    }

    /**
     * [fewchars description].
     *
     * @param [type] $s      [description]
     * @param [type] $lenght [description]
     *
     * @return [type] [description]
     */
    public static function fewchars($s, $lenght)
    {
        $str_to_count = strip_tags($s);
        if (strlen($str_to_count) <= $lenght) {
            return $s;
        }
        $s2  = mb_substr($str_to_count, 0, $lenght - 3, 'utf-8');
        $s2 .= '...';

        return $s2;
    }

    /**
     * redirect the url.
     *
     * @param [type] $url  [description]
     * @param int    $time [description]
     * @param bool   $html [description]
     *
     * @return [type] [description]
     */
    public static function redirect($url, $time = 0, $html = false, $overwrite = false)
    {
        $url = str_replace('com_', '', $url);

        $is_ajax_request = Registry::get('is_ajax_request');
        if ($is_ajax_request) {
            $template = Registry::get('template');

            $status = $template->release('status');

            // check already redirect exists
            $redirect = Registry::get('redirect');
            if ($redirect && $overwrite === false) {
                $url = $redirect;
            }

            $status['redirect'] = $url;
            $template->assign('status', $status);
            $template->assign('redirect', $url);
            Registry::set('redirect', $url);

            return true;
        }

        if ($html || headers_sent()) {
            echo  '<meta http-equiv="refresh" content="'.$time.'; url='.$url.'"/>';

            return true;
        }

        if ($time) {
            header('refresh:'.$time.';url='.str_replace('&amp;', '&', $url));
        } else {
            header('location:'.str_replace('&amp;', '&', $url));
        }
    }

    public static function location($url, $time = 0)
    {
        header('location:'.str_replace('&amp;', '&', $url), $time);
    }

    public static function specialchars($str)
    {
        $char   = ['&','"',"'"];
        $enti   = ['&amp;','&quot','&#039;'];
        $str    = str_replace($enti, $char, $str);
        $str    = str_replace($char, $enti, $str);

        return $str;
    }

    public static function cleanPath($path)
    {
        return realpath($path);
    }

    public static function get_include_contents($filename)
    {
        if (is_file($filename)) {
            ob_start();
            include $filename;
            $contents = ob_get_contents();
            ob_end_clean();

            return $contents;
        }

        return false;
    }

    public static function is_ssl()
    {
        if (isset($_SERVER['HTTPS'])) {
            if ('on' == strtolower($_SERVER['HTTPS'])) {
                return true;
            }
            if ('1' == $_SERVER['HTTPS']) {
                return true;
            }
        } elseif (isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'])) {
            return true;
        }

        return false;
    }

    public static function uniq_id($limit = 10, $prefix = '')
    {
        return $prefix.substr(uniqid(rand(), true), 0, $limit);
    }

    public static function currentUrl()
    {
        $pageURL = 'http';
        if ($_SERVER['HTTPS'] == 'on') {
            $pageURL .= 's';
        }
        $pageURL .= '://';
        if ($_SERVER['SERVER_PORT'] != '80') {
            $pageURL .= $_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].$_SERVER['REQUEST_URI'];
        } else {
            $pageURL .= $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
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
                default;
                header('Content-type: application/octet-stream');
                header('Content-Disposition: filename="'.$path_parts['basename'].'"');
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
     * @return string The IP address.
     */
    public static function ip()
    {
        $ip = '';
        $ip = $_SERVER['REMOTE_ADDR'];

        if (empty($ip) && isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            if (preg_match_all("#[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}#s", $_SERVER['HTTP_X_FORWARDED_FOR'], $addresses)) {
                foreach ($addresses[0] as $key => $val) {
                    if (!preg_match("#^(10|172\.16|192\.168)\.#", $val)) {
                        $ip = $val;
                        break;
                    }
                }
            }
        }

        if (empty($ip)) {
            if (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (isset($_SERVER['REMOTE_ADDR'])) {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        }
        $ip = preg_replace('#([^.0-9 ]*)#', '', $ip);

        return $ip;
    }

    public static function sessionid()
    {
        return $_COOKIE[session_name()];
    }

    public static function shuffle_assoc($array)
    {
        $keys = array_keys($array);
        shuffle($keys);

        return array_merge(array_flip($keys), $array);
    }

    public static function roundUp($value, $round = 5)
    {
        //$m  = round($value/$round)*$round;
        $m  = ceil(intval($value) / $round) * $round;
        $up =  0.01 * round($i * 100);

        return ['round' => $m,'up' => $up];
    }

    public static function parseQuery($var)
    {
        $var  = parse_url($var, PHP_URL_QUERY);
        $var  = html_entity_decode($var);
        $var  = explode('&', $var);
        $arr  = [];

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

    public static function sanitize($option, $type = 'component')
    {
        if ($type == 'module') {
            return self::sanitizeModule($option);
        }

        return self::sanitizeOption($option);
    }

    public static function sanitizeOption($option)
    {
        $option = strtolower($option);

        return (!empty($option) && strpos($option, 'com_') === false && strpos($option, 'mod_') === false) ? 'com_'.$option : $option;
    }

    public static function sanitizeModule($option)
    {
        $option = strtolower($option);

        return (!empty($option) && strpos($option, 'mod_') === false) ? 'mod_'.$option : $option;
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
        $attr       = [];
        $retarray   = [];

        // Lets grab all the key/value pairs using a regular expression
        preg_match_all('/([\w:-]+)[\s]?=[\s]?"([^"]*)"/i', $string, $attr);

        if (is_array($attr)) {
            $numPairs = count($attr[1]);
            for ($i = 0; $i < $numPairs; $i++) {
                $retarray[$attr[1][$i]] = $attr[2][$i];
            }
        }

        return $retarray;
    }

    public static function symlink($source, $dest)
    {
        if ($_SERVER['WINDIR'] || $_SERVER['windir']) {
            $source = str_replace('/', '\\', $source);
            $dest   = str_replace('/', '\\', $dest);

            return exec('mklink /j "'.$dest.'" "'.$source.'"');
        } else {
            return symlink($source, $dest);
        }
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
        $convert = Configure::read('datesettings.convert');

        if (!is_numeric($time)) {
            $parts = explode('/', $time);

            if ($parts[0] && strlen($parts[0]) != 4) {
                $time = str_replace('/', '-', trim($time));
            }

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

        return $dates;
    }

    public static function stripTags($data, $ignore = [])
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (in_array($key, $ignore)) {
                    $data[$key] = $value;
                    continue;
                }
                $data[$key] = self::stripTags($value, $ignore);
            }
        } else {
            $data = strip_tags($data);
        }

        return $data;
    }

    public static function xSafe($data, $encoding = 'UTF-8')
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::xSafe($value, $encoding);
            }
        } else {
            $data = htmlspecialchars($data, ENT_QUOTES | ENT_HTML401, $encoding);
        }

        return $data;
    }

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
    }
}
