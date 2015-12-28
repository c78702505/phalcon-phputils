<?php

namespace PhpPlus\Response;


use PhpPlus\Common\ObjectBase;

/**
 * Response基类
 * @package PhpPlus\Response
 */
class Base extends ObjectBase
{
    private $content = '';

    /**
     * 获取返回内容
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * 设置返回内容
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * 设置返回内容类型
     * @param string $contentType
     * @param null|string $charset
     */
    public function setContentType($contentType, $charset = null)
    {
        $c = "Content-Type: $contentType;";
        if($charset != null) {
            $c = $c . "charset=$charset";
        }
        header($c);
    }
}