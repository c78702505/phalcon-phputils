<?php

namespace PhpPlus\Common;


/**
 * 字符串处理
 * @package PhpPlus\Common
 */
class String
{
    /**
     * 生成GUID
     * @return string GUID STRING
     */
    public static function guid()
    {
        if (function_exists('com_create_guid')){
            return com_create_guid();
        }else{
            mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45);
            $uuid = chr(123)
                .substr($charid, 0, 8).$hyphen
                .substr($charid, 8, 4).$hyphen
                .substr($charid,12, 4).$hyphen
                .substr($charid,16, 4).$hyphen
                .substr($charid,20,12)
                .chr(125);
            return $uuid;
        }
    }

    /**
     * string starts with
     * @param $haystack
     * @param $needle
     * @return bool
     */
    public static function startsWith($haystack, $needle)
    {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

    /**
     * string ends with
     * @param $haystack
     * @param $needle
     * @return bool
     */
    public static function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length == 0) {
            return TRUE;
        }
        $start  = $length * -1;
        return (substr($haystack, $start) === $needle);
    }

    /**
     * 防C# string.format
     * @return string
     */
    public static function format() {
        $args = func_get_args();
        $format = array_shift($args);

        preg_match_all('/(?=\{)\{(\d+)\}(?!\})/', $format, $matches, PREG_OFFSET_CAPTURE);
        $offset = 0;
        foreach ($matches[1] as $data) {
            $i = $data[0];
            $format = substr_replace($format, @$args[$i], $offset + $data[1] - 1, 2 + strlen($i));
            $offset += strlen(@$args[$i]) - 2 - strlen($i);
        }

        return $format;
    }
}