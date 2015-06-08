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
class Form
{
    /**
     * Select.
     *
     * Generates a <select> element based on the given parameters
     *
     * @param array
     *
     * @return string
     */
    public function select($field, $values = null, $options = [], $attributes = [])
    {
        if (is_array($field)) {
            $attributes             = $field;
            $attributes['selected'] = empty($attributes['value']) ? '' : $attributes['value'];
        } else {
            $attributes['name']     = (string) $field;
            $attributes['selected'] = $values;
            $attributes['options']  = $options;
        }
        unset($attributes['value']);

        // Get the options then unset them from the array
        $options = $attributes['options'];
        unset($attributes['options']);
        // Get the selected options then unset it from the array
        $selected = ($attributes['selected'] == '') ? [] : array_values((array) $attributes['selected']);
        unset($attributes['selected']);

        $input = PHP_EOL;
        foreach ($options as $key => $val) {
            if (is_array($val)) {
                $optgroup = PHP_EOL;
                foreach ($val as $opt_key => $opt_val) {
                    $opt_attr                                      = ['value' => $opt_key];
                    (in_array($opt_key, $selected)) && $opt_attr[] = 'selected';
                    $optgroup .= str_repeat("\t", 2);
                    $optgroup .= self::html_tag('option', $opt_attr, $opt_val).PHP_EOL;
                }
                $optgroup .= str_repeat("\t", 1);
                $input .= str_repeat("\t", 1).self::html_tag('optgroup', ['label' => $key], $optgroup, false).PHP_EOL;
            } else {
                $opt_attr                                  = ['value' => $key];
                (in_array($key, $selected)) && $opt_attr[] = 'selected';
                $input .= str_repeat("\t", 1);
                $input .= self::html_tag('option', $opt_attr, $val).PHP_EOL;
            }
        }
        $input .= str_repeat("\t", 0);

        if ($attributes['onlyoptions']) {
            return $input;
        }

        return self::html_tag('select', $attributes, $input);
    }

    /* helper functions */

    /**
     * Takes an array of attributes and turns it into a string for an html tag.
     *
     * @param array $attr
     *
     * @return string
     */
    public function array_to_attr($attr)
    {
        $attr_str = '';

        foreach ($attr as $property => $value) {
            // If the key is numeric then it must be something like selected="selected"
            if (is_numeric($property)) {
                $property = $value;
            }

            if (in_array($property, ['value', 'alt', 'title'])) {
                $value = htmlentities($value, ENT_QUOTES, 'utf-8');
            }

            $attr_str .= $property.'="'.$value.'" ';
        }
        // We strip off the last space for return
        return trim($attr_str);
    }

    /**
     * Create a XHTML tag.
     *
     * @param	string			The tag name
     * @param	array|string	The tag attributes
     * @param	string|bool		The content to place in the tag, or false for no closing tag
     *
     * @return string
     */
    public function html_tag($tag, $attr = [], $content = false, $unsetlabel = true)
    {
        if ($unsetlabel) {
            unset($attr['label']);
        }

        $has_content = (bool) ($content !== false && $content !== null);
        $html        = '<'.$tag;
        $html .= (!empty($attr)) ? ' '.(is_array($attr) ? self::array_to_attr($attr) : $attr) : '';
        $html .= $has_content ? '>' : ' />';
        $html .= $has_content ? $content.'</'.$tag.'>' : '';

        return $html;
    }

    public function token()
    {
        return '<input type="hidden" name="form_token" value="'.token().'" />';
    }
}
