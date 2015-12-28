<?php

namespace PhpPlus\Common;


/**
 * 随机处理
 * @package PhpPlus\Common
 */
class Random
{
    /**
     * 获取长ID，当前年月日+100000到999999之间的随机数
     * @param bool $long 增加为年月日时分秒+1000到9999之间的随机数
     * @return string
     */
    public static function longId($long=false)
    {
        if($long)
            return date('YmdHis') . mt_rand(1000,9999);
        else
            return date('Ymd') . mt_rand(100000,999999);
    }

    /**
     * 获取超长ID的字符串形式
     * @return string
     */
    public static function longIdStr()
    {
        $number = md5(time() . mt_rand(100000, 999999));
        $number = base_convert(substr($number, 8, 16), 16, 10);
        return BaseConvert::to62($number);
    }

    /**
     * 生成指定长度的随机数字
     * @param int $len
     * @return int
     */
    public static function number($len = 4)
    {
        $res = '';
        while(strlen($res) < $len) {
            $res = $res . mt_rand(1000, 9999);
        }
        return substr($res, 0, $len);
    }

    /**
     * 随机字符串
     * @param int $len
     * @return string
     */
    public static function str($len = 8)
    {
        //生成一个包含 大写英文字母, 小写英文字母, 数字 的数组
        $arr = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'));
        $str = '';
        $arr_len = count($arr);
        for ($i=0; $i<$len; $i++) {
            $rand = mt_rand(0, $arr_len-1);
            $str.=$arr[$rand];
        }
        return $str;
    }
}