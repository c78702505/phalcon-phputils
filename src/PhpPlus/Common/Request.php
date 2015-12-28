<?php

namespace PhpPlus\Common;


/**
 * request相关处理
 * @package PhpPlus\Common
 */
class Request
{
    /**
     * 获取用户IP
     * @return string IP String
     */
    public static function userIp() {
        $realIp = '';
        if (isset ( $_SERVER )) {
            if (isset ( $_SERVER ['HTTP_X_FORWARDED_FOR'] )) {
                $realIp = $_SERVER ['HTTP_X_FORWARDED_FOR'];
            } elseif (isset ( $_SERVER ['HTTP_CLIENT_IP'] )) {
                $realIp = $_SERVER ['HTTP_CLIENT_IP'];
            } else {
                $realIp = $_SERVER ['REMOTE_ADDR'];
            }
        } else {
            if (getenv ( 'HTTP_X_FORWARDED_FOR' )) {
                $realIp = getenv ( 'HTTP_X_FORWARDED_FOR' );
            } elseif (getenv ( 'HTTP_CLIENT_IP' )) {
                $realIp = getenv ( 'HTTP_CLIENT_IP' );
            } else {
                $realIp = getenv ( 'REMOTE_ADDR' );
            }
        }
        return $realIp;
    }

    /**
     * 获取http 所有头信息
     * @return string
     */
    public static function getAllHeaders() {
        if (!function_exists('getallheaders'))
        {
            $headers = '';
            foreach ($_SERVER as $name => $value)
            {
                if (substr($name, 0, 5) == 'HTTP_')
                {
                    $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                }
            }
            return $headers;
        }
        return getallheaders();
    }

}