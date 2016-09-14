<?php

/*
 * This file is part of the Speedwork package.
 *
 * (c) Sankar <sankar.suda@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Speedwork\Util\Traits;

/**
 * All wordwrap methods separated from Str class.
 *
 * @author sankar <sankar.sua@gmail.com>
 */
trait WordTrait
{
    /**
     * Wraps text to a specific width, can optionally wrap at word breaks.
     *
     * ### Options
     *
     * - `width` The width to wrap to. Defaults to 72.
     * - `wordWrap` Only wrap on words breaks (spaces) Defaults to true.
     * - `indent` String to indent with. Defaults to null.
     * - `indentAt` 0 based index to start indenting at. Defaults to 0.
     *
     * @param string    $text    The text to format
     * @param array|int $options Array of options to use, or an integer to wrap the text to
     *
     * @return string Formatted text
     */
    public static function wrap($text, $options = [])
    {
        if (is_numeric($options)) {
            $options = ['width' => $options];
        }
        $options += ['width' => 72, 'wordWrap' => true, 'indent' => null, 'indentAt' => 0];
        if ($options['wordWrap']) {
            $wrapped = self::wordWrap($text, $options['width'], "\n");
        } else {
            $wrapped = trim(chunk_split($text, $options['width'] - 1, "\n"));
        }
        if (!empty($options['indent'])) {
            $chunks = explode("\n", $wrapped);
            for ($i = $options['indentAt'], $len = count($chunks); $i < $len; ++$i) {
                $chunks[$i] = $options['indent'].$chunks[$i];
            }
            $wrapped = implode("\n", $chunks);
        }

        return $wrapped;
    }

    /**
     * Wraps a complete block of text to a specific width, can optionally wrap
     * at word breaks.
     *
     * ### Options
     *
     * - `width` The width to wrap to. Defaults to 72.
     * - `wordWrap` Only wrap on words breaks (spaces) Defaults to true.
     * - `indent` String to indent with. Defaults to null.
     * - `indentAt` 0 based index to start indenting at. Defaults to 0.
     *
     * @param string    $text    The text to format
     * @param array|int $options Array of options to use, or an integer to wrap the text to
     *
     * @return string Formatted text
     */
    public static function wrapBlock($text, $options = [])
    {
        if (is_numeric($options)) {
            $options = ['width' => $options];
        }
        $options += ['width' => 72, 'wordWrap' => true, 'indent' => null, 'indentAt' => 0];

        if (!empty($options['indentAt']) && $options['indentAt'] === 0) {
            $indentLength     = !empty($options['indent']) ? strlen($options['indent']) : 0;
            $options['width'] = $options['width'] - $indentLength;

            return self::wrap($text, $options);
        }

        $wrapped = self::wrap($text, $options);

        if (!empty($options['indent'])) {
            $indent = mb_strlen($options['indent']);
            $chunks = explode("\n", $wrapped);
            $count  = count($chunks);
            if ($count < 2) {
                return $wrapped;
            }
            $toRewrap = '';
            for ($i = $options['indentAt']; $i < $count; ++$i) {
                $toRewrap .= mb_substr($chunks[$i], $indent).' ';
                unset($chunks[$i]);
            }
            $options['width'] -= $indent;
            $options['indentAt'] = 0;
            $rewrapped           = self::wrap($toRewrap, $options);
            $newChunks           = explode("\n", $rewrapped);

            $chunks  = array_merge($chunks, $newChunks);
            $wrapped = implode("\n", $chunks);
        }

        return $wrapped;
    }

    /**
     * Unicode and newline aware version of wordwrap.
     *
     * @param string $text  The text to format
     * @param int    $width The width to wrap to. Defaults to 72
     * @param string $break The line is broken using the optional break parameter. Defaults to '\n'
     * @param bool   $cut   If the cut is set to true, the string is always wrapped at the specified width
     *
     * @return string Formatted text
     */
    public static function wordWrap($text, $width = 72, $break = "\n", $cut = false)
    {
        $paragraphs = explode($break, $text);
        foreach ($paragraphs as &$paragraph) {
            $paragraph = static::wordWrapSingle($paragraph, $width, $break, $cut);
        }

        return implode($break, $paragraphs);
    }

    /**
     * Unicode aware version of wordwrap as helper method.
     *
     * @param string $text  The text to format
     * @param int    $width The width to wrap to. Defaults to 72
     * @param string $break The line is broken using the optional break parameter. Defaults to '\n'
     * @param bool   $cut   If the cut is set to true, the string is always wrapped at the specified width
     *
     * @return string Formatted text
     */
    protected static function wordWrapSingle($text, $width = 72, $break = "\n", $cut = false)
    {
        if ($cut) {
            $parts = [];
            while (mb_strlen($text) > 0) {
                $part    = mb_substr($text, 0, $width);
                $parts[] = trim($part);
                $text    = trim(mb_substr($text, mb_strlen($part)));
            }

            return implode($break, $parts);
        }

        $parts = [];
        while (mb_strlen($text) > 0) {
            if ($width >= mb_strlen($text)) {
                $parts[] = trim($text);
                break;
            }

            $part     = mb_substr($text, 0, $width);
            $nextChar = mb_substr($text, $width, 1);
            if ($nextChar !== ' ') {
                $breakAt = mb_strrpos($part, ' ');
                if ($breakAt === false) {
                    $breakAt = mb_strpos($text, ' ', $width);
                }
                if ($breakAt === false) {
                    $parts[] = trim($text);
                    break;
                }
                $part = mb_substr($text, 0, $breakAt);
            }

            $part    = trim($part);
            $parts[] = $part;
            $text    = trim(mb_substr($text, mb_strlen($part)));
        }

        return implode($break, $parts);
    }

    /**
     * Highlights a given phrase in a text. You can specify any expression in highlighter that
     * may include the \1 expression to include the $phrase found.
     *
     * ### Options:
     *
     * - `format` The piece of HTML with that the phrase will be highlighted
     * - `html` If true, will ignore any HTML tags, ensuring that only the correct text is highlighted
     * - `regex` a custom regex rule that is used to match words, default is '|$tag|iu'
     *
     * @param string       $text    Text to search the phrase in
     * @param string|array $phrase  The phrase or phrases that will be searched
     * @param array        $options An array of HTML attributes and options
     *
     * @return string The highlighted text
     */
    public static function highlight($text, $phrase, array $options = [])
    {
        if (empty($phrase)) {
            return $text;
        }

        $defaults = [
            'format' => '<span class="highlight">\1</span>',
            'html'   => false,
            'regex'  => '|%s|iu',
        ];
        $options += $defaults;
        extract($options);

        if (is_array($phrase)) {
            $replace = [];
            $with    = [];

            foreach ($phrase as $key => $segment) {
                $segment = '('.preg_quote($segment, '|').')';
                if ($html) {
                    $segment = "(?![^<]+>)$segment(?![^<]+>)";
                }

                $with[]    = (is_array($format)) ? $format[$key] : $format;
                $replace[] = sprintf($options['regex'], $segment);
            }

            return preg_replace($replace, $with, $text);
        }

        $phrase = '('.preg_quote($phrase, '|').')';
        if ($html) {
            $phrase = "(?![^<]+>)$phrase(?![^<]+>)";
        }

        return preg_replace(sprintf($options['regex'], $phrase), $format, $text);
    }

    /**
     * Truncates text starting from the end.
     *
     * Cuts a string to the length of $length and replaces the first characters
     * with the ellipsis if the text is longer than length.
     *
     * ### Options:
     *
     * - `ellipsis` Will be used as Beginning and prepended to the trimmed string
     * - `exact` If false, $text will not be cut mid-word
     *
     * @param string $text    String to truncate
     * @param int    $length  Length of returned string, including ellipsis
     * @param array  $options An array of options
     *
     * @return string Trimmed string
     */
    public static function tail($text, $length = 100, array $options = [])
    {
        $default = [
            'ellipsis' => '...', 'exact' => true,
        ];
        $options += $default;

        if (mb_strlen($text) <= $length) {
            return $text;
        }

        $truncate = mb_substr($text, mb_strlen($text) - $length + mb_strlen($options['ellipsis']));
        if (!$options['exact']) {
            $spacepos = mb_strpos($truncate, ' ');
            $truncate = $spacepos === false ? '' : trim(mb_substr($truncate, $spacepos));
        }

        return $options['ellipsis'].$truncate;
    }

    /**
     * Truncates text.
     *
     * Cuts a string to the length of $length and replaces the last characters
     * with the ellipsis if the text is longer than length.
     *
     * ### Options:
     *
     * - `ellipsis` Will be used as ending and appended to the trimmed string
     * - `exact` If false, $text will not be cut mid-word
     * - `html` If true, HTML tags would be handled correctly
     *
     * @param string $text    String to truncate
     * @param int    $length  Length of returned string, including ellipsis
     * @param array  $options An array of HTML attributes and options
     *
     * @return string Trimmed string
     */
    public static function truncate($text, $length = 100, array $options = [])
    {
        $default = [
            'ellipsis' => '...', 'exact' => true, 'html' => false,
        ];
        if (!empty($options['html']) && strtolower(mb_internal_encoding()) === 'utf-8') {
            $default['ellipsis'] = "\xe2\x80\xa6";
        }
        $options += $default;
        extract($options);

        if ($html) {
            if (mb_strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
                return $text;
            }
            $totalLength = mb_strlen(strip_tags($ellipsis));
            $openTags    = [];
            $truncate    = '';

            preg_match_all('/(<\/?([\w+]+)[^>]*>)?([^<>]*)/', $text, $tags, PREG_SET_ORDER);
            foreach ($tags as $tag) {
                if (!preg_match('/img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param/s', $tag[2])) {
                    if (preg_match('/<[\w]+[^>]*>/s', $tag[0])) {
                        array_unshift($openTags, $tag[2]);
                    } elseif (preg_match('/<\/([\w]+)[^>]*>/s', $tag[0], $closeTag)) {
                        $pos = array_search($closeTag[1], $openTags);
                        if ($pos !== false) {
                            array_splice($openTags, $pos, 1);
                        }
                    }
                }
                $truncate .= $tag[1];

                $contentLength = mb_strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $tag[3]));
                if ($contentLength + $totalLength > $length) {
                    $left           = $length - $totalLength;
                    $entitiesLength = 0;
                    if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', $tag[3], $entities, PREG_OFFSET_CAPTURE)) {
                        foreach ($entities[0] as $entity) {
                            if ($entity[1] + 1 - $entitiesLength <= $left) {
                                --$left;
                                $entitiesLength += mb_strlen($entity[0]);
                            } else {
                                break;
                            }
                        }
                    }

                    $truncate .= mb_substr($tag[3], 0, $left + $entitiesLength);
                    break;
                } else {
                    $truncate .= $tag[3];
                    $totalLength += $contentLength;
                }
                if ($totalLength >= $length) {
                    break;
                }
            }
        } else {
            if (mb_strlen($text) <= $length) {
                return $text;
            }
            $truncate = mb_substr($text, 0, $length - mb_strlen($ellipsis));
        }
        if (!$options['exact']) {
            $spacepos = mb_strrpos($truncate, ' ');
            if ($html) {
                $truncateCheck = mb_substr($truncate, 0, $spacepos);
                $lastOpenTag   = mb_strrpos($truncateCheck, '<');
                $lastCloseTag  = mb_strrpos($truncateCheck, '>');
                if ($lastOpenTag > $lastCloseTag) {
                    preg_match_all('/<[\w]+[^>]*>/s', $truncate, $lastTagMatches);
                    $lastTag  = array_pop($lastTagMatches[0]);
                    $spacepos = mb_strrpos($truncate, $lastTag) + mb_strlen($lastTag);
                }
                $bits = mb_substr($truncate, $spacepos);
                preg_match_all('/<\/([a-z]+)>/', $bits, $droppedTags, PREG_SET_ORDER);
                if (!empty($droppedTags)) {
                    if (!empty($openTags)) {
                        foreach ($droppedTags as $closingTag) {
                            if (!in_array($closingTag[1], $openTags)) {
                                array_unshift($openTags, $closingTag[1]);
                            }
                        }
                    } else {
                        foreach ($droppedTags as $closingTag) {
                            $openTags[] = $closingTag[1];
                        }
                    }
                }
            }
            $truncate = mb_substr($truncate, 0, $spacepos);

            // If truncate still empty, then we don't need to count ellipsis in the cut.
            if (mb_strlen($truncate) === 0) {
                $truncate = mb_substr($text, 0, $length);
            }
        }

        $truncate .= $ellipsis;

        if ($html) {
            foreach ($openTags as $tag) {
                $truncate .= '</'.$tag.'>';
            }
        }

        return $truncate;
    }
}
