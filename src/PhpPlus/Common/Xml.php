<?php

namespace PhpPlus\Common;


/**
 * XML处理
 * @package PhpPlus\Common
 */
class Xml
{
    /**
     * xml转换为数组
     * @param string $xml
     * @return bool|mixed
     */
    public static function parseArray($xml)
    {
        try {
            //将XML转为array
            $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
            return $array_data;
        }catch (\Exception $e)
        {
            return false;
        }
    }

    /**
     * 转换数组为xml
     * @param array $data
     * @param string $rootName
     * @return string
     */
    public static function convertArray($data, $rootName='xml')
    {
        $xmlRoot = new \SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\" ?><$rootName></$rootName>");
        self::arrayToXml($data, $xmlRoot);
        $str = $xmlRoot->asXML();
        return $str === false ? '' : $str;
    }

    /**
     * 输入写入到SimpleXMLElement对象中
     * @param $body
     * @param \SimpleXMLElement $xmlRoot
     */
    protected static function arrayToXml($body, \SimpleXMLElement &$xmlRoot)
    {
        foreach($body as $k => $v)
        {
            $key = is_numeric($k) ? "item$k" : $k;
            if(is_array($v))
            {
                $sub = $xmlRoot->addChild("$key");
                self::arrayToXml($v, $sub);
            }
            else
            {
                $xmlRoot->addChild("$key", "$v");
            }
        }
    }
}