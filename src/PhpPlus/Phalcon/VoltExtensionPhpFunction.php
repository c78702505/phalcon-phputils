<?php

namespace PhpPlus\Phalcon;


/**
 * phaclon volt extension php function
 * @package PhpPlus\Utils\Phalcon
 */
class VoltExtensionPhpFunction
{
    /**
     * This method is called on any attempt to compile a function call
     */
    public function compileFunction($name, $arguments)
    {
        if (function_exists($name)) {
            return $name . '('. $arguments . ')';
        }
    }

}