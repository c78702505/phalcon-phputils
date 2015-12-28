<?php

namespace PhpPlus\Common;


/**
 * 用户登录处理
 * @package PhpPlus\Common
 * @property int $id 用户标识
 * @property string $name 用户显示名称
 */
class UserIdentity extends ObjectBase
{
    /**
     * cookie 中的键名
     * @var string
     */
    public $cookieKey = 'ICE_U';
    /**
     * @var callable|null
     */
    public $cookieGet = null;
    /**
     * @var callable|null
     */
    public $cookieSet = null;
    /**
     * @var callable|null
     */
    public $signFunc = null;

    /**
     * @param null|callback $cookieGet
     * @param null|callback $cookieSet
     */
    public function __construct($cookieGet = null, $cookieSet = null)
    {
        $this->cookieGet = $cookieGet;
        $this->cookieSet = $cookieSet;
    }

    /**
     * 是否登录
     * @return bool
     */
    public function isLogin()
    {
        if(is_array($this->data) && isset($this->data['id']) && $this->data['id'] > 0)
            return true;
        return $this->loadCookie();
    }

    /**
     * 在cookie中获取用户信息
     * @return bool
     */
    public function loadCookie()
    {
        $cookieGet = $this->cookieGet;
        $cookie = is_callable($cookieGet) ? $cookieGet($this->cookieKey) : null;
        if(!$cookie) {
            return false;
        }
        $cookie = str_replace("\0","",$cookie);
        $data = json_decode($cookie, true);
        if(!$data) {
            return false;
        }
        // 判断签名
        $sign = isset($data['sign']) ? $data['sign'] : null;
        unset($data['sign']);
        if($sign != $this->hashPassword($data))
        {
            return false;
        }
        foreach($data as $k=>$v) {
            $this->data[$k] = $v;
        }
        return true;
    }

    /**
     * 保存登录信息
     * @param $userData
     * @return bool
     */
    public function saveUserLogin($userData)
    {
        $cookieSet = $this->cookieSet;
        if(!is_callable($cookieSet)) {
            return false;
        }
        $userData['ts'] = time();
        $userData['sign'] = $this->hashPassword($userData);
        $cookieSet($this->cookieKey, json_encode($userData));
        unset($userData['sign']);
        foreach($userData as $k=>$v) {
            $this->data[$k] = $v;
        }
        return true;
    }

    /**
     * 退出登录
     */
    public function logout()
    {
        $cookieSet = $this->cookieSet;
        if(is_callable($cookieSet)) {
            $cookieSet($this->cookieKey, '');
        }
    }

    /**
     * 密码加密
     * @param $v
     * @return string
     */
    public function hashPassword($v)
    {
        $func = $this->signFunc;
        if($this->signFunc == null || is_callable($func))
        {
            $key = 'a2e957d1517c0bba66f861b525d87a53';
            $str = is_string($v) ? $v : json_encode($v);
            return strtolower(md5($str . $key));
        } else {
            return $func($v);
        }
    }
}