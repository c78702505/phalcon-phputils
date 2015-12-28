<?php

namespace PhpPlus\Common;


/**
 * 进制转换，最大支持到62进制
 * @package PhpPlus\Common
 */
class BaseConvert
{
    const dict = '0123456789abcdefghABCDEFGHijklmnopqrIJKLMNOPQRstuvwxyzSTUVWXYZ'; // 故意顺序打乱增加破解难度

    /**
     * 十进制数转换为62进制
     * @param $num
     * @return string
     */
    public static function to62($num)
    {
        return self::decTo($num, 62);
    }

    /**
     * 62进制数转换为十进制
     * @param $num
     * @return int|string
     */
    public static function from62($num)
    {
        return self::decFrom($num, 62);
    }

    /**
     * 十进制数转换成其他进制
     * @param $num
     * @param int $to
     * @return string
     */
    public static function decTo($num, $to=62)
    {
        if($to == 10 || $to > 62 || $to < 2) {
            return $num;
        }
        $dict = self::dict;
        $ret = '';
        do {
            $ret = $dict[bcmod($num, $to)] . $ret;
            $num = bcdiv($num, $to);
        } while($num > 0);
        return $ret;
    }

    /**
     * 其他进制数转换成十进制
     * @param $num
     * @param int $from
     * @return int|string
     */
    public static function decFrom($num, $from=62)
    {
        if ($from == 10 || $from > 62 || $from < 2) {
            return $num;
        }
        $num = strval($num);
        $dict = self::dict;
        $len = strlen($num);
        $dec = 0;
        for($i = 0; $i < $len; $i++) {
            $pos = strpos($dict, $num[$i]);
            if ($pos >= $from) {
                continue; // 如果出现非法字符，会忽略掉。比如16进制中出现w、x、y、z等
            }
            $dec = bcadd(bcmul(bcpow($from, $len - $i - 1), $pos), $dec);
        }
        return $dec;
    }

    /**
     * 数字的任意进制转换
     * @param $number
     * @param int $to 目标进制数
     * @param int $from 源进制数
     * @return int|string
     */
    public static function radix($number, $to=62, $from=10)
    {
        $number = self::decFrom($number, $from);
        $number = self::decTo($number, $to);
        return $number;
    }

    /**
     * 数字编码并按指定长度补位
     * @param $num
     * @param int $len
     * @return string
     */
    public static function numberDec($num, $len=0) {
        $numStr = strval($num);
        $slen = strlen($numStr);
        $tagNo = '5'; //没有补位
        $tagYes = '4'; // 已经补位 ,补位后第二个字符为补位长度
        if($len == 0) {
            return $tagNo . self::to62($num);
        }
        if($len <= $slen) {
            return $tagNo . self::to62($numStr);
        }
        // 补位，补位长度不能操过62
        $bl = $len - $slen;
        $numStr = Random::number($bl) . $numStr;
        return $tagYes . self::to62($bl) . self::to62($numStr);
    }

    /**
     * 数字解码
     * @param $numStr
     * @return int|string
     */
    public static function numberUndec($numStr) {
        $tagNo = '5'; //没有补位
        $tagYes = '4'; // 已经补位 ,补位后第二个字符为补位长度
        $tag = substr($numStr, 0, 1);
        if($tag == $tagNo) {
            return self::from62(substr($numStr, 1));
        }
        // 补位处理
        $len = intval(self::from62(substr($numStr, 1, 1)));
        $num = self::from62(substr($numStr, 2));
        return substr($num, $len);
    }
}