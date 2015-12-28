<?php

namespace PhpPlus\Common;


/**
 * URL相关处理
 * @package PhpPlus\Common
 */
class Url
{
    /**
     * url build query string
     * @param string $url
     * @param array $params
     * @return string
     */
    public static function urlAddParams($url, $params)
    {
        if(empty($params)) {
            return $url;
        }
        $body = '';
        if(is_array($params)) {
            $body = http_build_query($params);
        } else if($params) {
            $body = strval($params);
        }
        $url = $url . (strpos($url, '?') ? '&' : '?');
        return $url . $body;
    }

    /**
     * 当前URL的http协议和域名部分
     * @return string
     */
    public static function getHttpDomain()
    {
        $ssl        = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? true:false;
        $sp         = strtolower($_SERVER['SERVER_PROTOCOL']);
        $protocol   = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
        $port       = $_SERVER['SERVER_PORT'];
        $port       = ((!$ssl && $port == '80') || ($ssl && $port == '443')) ? '' : ':'.$port;
        $host       = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
        return $protocol . '://' . $host . $port;
    }

    /**
     * 当前URL
     * @return string
     */
    public static function current()
    {
        $ssl        = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? true:false;
        $sp         = strtolower($_SERVER['SERVER_PROTOCOL']);
        $protocol   = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
        $port       = $_SERVER['SERVER_PORT'];
        $port       = ((!$ssl && $port == '80') || ($ssl && $port == '443')) ? '' : ':'.$port;
        $host       = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
        return $protocol . '://' . $host . $port . $_SERVER['REQUEST_URI'];
    }
}