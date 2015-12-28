<?php

namespace PhpPlus\Common;

/**
 * 多重继承
 * @package PhpPlus\Common
 */
abstract class ExtensionBridge
{
    private $classObj = [];
    public $_this;

    public function __construct()
    {
        $this->_this = $this;
    }

    public function addClass($obj)
    {
        $this->classObj[] = $obj;
    }

    public function __get($varName)
    {
        $getter = 'get' . $varName;
        if (method_exists($this->_this, $getter)) {
            return $this->_this->$getter();
        }
        foreach($this->classObj as $obj) {
            if(method_exists($obj, $getter)) {
                return $obj->$getter();
            }
            if(property_exists($obj, $varName)) {
                return $obj->$varName;
            }
        }
        throw new \Exception("This property var {$varName} doesn't exists and method {$getter} doesn't exists.");
    }

    public function __set($varName, $value)
    {
        $setter = 'set' . $varName;
        if (method_exists($this->_this, $setter)) {
            $this->_this->$setter($value);
        }
        foreach($this->classObj as $obj) {
            if(method_exists($obj, $setter)) {
                $obj->$setter($value);
                return;
            }
            if(property_exists($obj, $varName)) {
                $obj->$varName = $value;
            }
        }
        throw new \Exception("This property var {$varName} doesn't exists and method {$setter} doesn't exists.");
    }

    public function __call($method, $args)
    {
        if(method_exists($this->_this, $method)) {
            return call_user_func_array([$this->_this, $method], $args);
        }
        foreach($this->classObj as $obj) {
            if(method_exists($obj, $method)) {
                return call_user_func_array([$obj, $method], $args);
            }
        }
        throw new \Exception("This method {$method} doesn't exists.");
    }
}