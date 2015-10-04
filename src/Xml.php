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

use StdClass;

/**
 * @author sankar <sankar.suda@gmail.com>
 */
class Xml
{
    public static function build($data, $block = 'nodes', $name = 'node')
    {
        if (is_array($data)) {
            return static::fromArray($data, $block, $name);
        }

        if (is_object($data)) {
            return static::fromObj($data, $block, $name);
        }
    }

    public static function fromObj(StdClass $obj, $block = 'nodes', $name = 'node')
    {
        return self::fromArray(get_object_vars($obj), $block, $name);
    }

    public static function fromArray($array, $node_block = 'nodes', $node_name = 'node')
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>';

        $xml .= '<'.$node_block.'>';
        $xml .= self::generate($array, $node_name);
        $xml .= '</'.$node_block.'>';

        return $xml;
    }

    protected static function generate($array, $node_name)
    {
        $xml = '';

        if (is_array($array) || is_object($array)) {
            foreach ($array as $key => $value) {
                if (is_numeric($key)) {
                    $key = $node_name;
                }

                $xml .= '<'.$key.'>'.self::generate($value, $node_name).'</'.$key.'>';
            }
        } else {
            $xml = htmlspecialchars($array, ENT_QUOTES);
        }

        return $xml;
    }
}
