<?php

namespace PhpPlus\Common;


/**
 * 对象基类
 * @package PhpPlus\Common
 */
class ObjectBase
{
    /**
     * @var array 数据存储
     */
    protected $data = [];

    /**
     * @return string 类名
     */
    public static function className()
    {
        return get_called_class();
    }

    public function __construct($config = [])
    {
        if (!empty($config)) {
            foreach ($config as $name => $value) {
                $this->$name = $value;
            }
        }
        $this->onCreated();
    }

    /**
     * 初始化
     */
    protected function onCreated()
    {
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function __get($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->$getter();
        } elseif(isset($this->data[$name])) {
            return $this->data[$name];
        }
        return null;
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            $this->$setter($value);
        }
        else
        {
            $this->data[$name] = $value;
        }
    }

    public function __isset($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->$getter() !== null;
        }
        else
        {
            return isset($this->data[$name]);
        }
    }

    public function __unset($name)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            $this->$setter(null);
        }
        else
        {
            unset($this->data[$name]);
        }
    }

    public function hasMethod($name)
    {
        return method_exists($this, $name);
    }

    /**
     * 获取类的实例化
     * @param string|object $class
     * @param array $params
     * @return mixed|object
     */
    public static function newInstance($class, array $params=[]) {
        if (is_callable($class))
        {
            return self::callFunction($class, $params);
        }

        switch (count($params))
        {
            case 0:
                return new $class();
            case 1:
                return new $class($params[0]);
            case 2:
                return new $class($params[0], $params[1]);
            case 3:
                return new $class($params[0], $params[1], $params[2]);
            case 4:
                return new $class($params[0], $params[1], $params[2], $params[3]);
            case 5:
                return new $class($params[0], $params[1], $params[2], $params[3], $params[4]);
            default:
                $refClass = new \ReflectionClass($class);
                return $refClass->newInstanceArgs($params);
        }
    }

    /**
     * 执行方法
     * @param string $func
     * @param array $params
     * @return mixed
     */
    public static function callFunction($func, array &$params=[]) {
        switch (count($params)) {
            case 0:
                return $func();
            case 1:
                return $func($params[0]);
            case 2:
                return $func($params[0], $params[1]);
            case 3:
                return $func($params[0], $params[1], $params[2]);
            case 4:
                return $func($params[0], $params[1], $params[2], $params[3]);
            case 5:
                return $func($params[0], $params[1], $params[2], $params[3], $params[4]);
            default:
                return call_user_func_array($func, $params);
        }
    }
}