<?php

namespace PhpPlus\Common;

/**
 * 文件后缀名获取
 * @package PhpPlus\Common
 */
class FileExtension
{
    protected static $maps = [
        '255216' => 'jpg',
        '13780' => 'png',
        '7173' => 'gif',
        '6677' => 'bmp',
        '7368' => 'mp3',
        '3533' => 'amr',
        '8273' => 'wav',
        '4838' => 'wma',
        '239187' => 'txt',
        '208207' => 'xls,doc,ppt',
        '6063' => 'xml',
        '6033' => 'htm,html',
        '4742' => 'js',
        '8075' => 'xlsx,zip,pptx,mmap',
        '8297' => 'rar',
        '7790' => 'exe,dll',
        '5666' => 'psd',
        '255254' => 'pdp',
        '10056' => 'bt'
    ];

    /**
     * 通过文件流获取文件类型
     * @param $content
     * @return null|string
     */
    public static function byStream($content)
    {
        $stream = @unpack('C2chars', substr($content, 0, 2));
        $code = intval($stream['chars1'] . $stream['chars2']);
        return isset(self::$maps[$code]) ? self::$maps[$code] : null;
    }

    /**
     * 通过文件路径获取文件类型
     * @param $filePath
     * @return null|string
     */
    public static function byFilePath($filePath)
    {
        $fp = fopen($filePath, 'rb');
        $bin = fread($fp, 2); // 读2字节
        fclose($fp);
        return self::byStream($bin);
    }
}