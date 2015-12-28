<?php

namespace PhpPlus\Response;


/**
 * 处理结果返回
 * @package PhpPlus\Response
 */
class ResultJson extends Base
{
    /**
     * @var int 结果状态值
     */
    public $status = ResultJsonStatus::SUCCESS;
    /**
     * @var string 错误信息
     */
    public $errmsg = '';
    /**
     * @var int 时间戳
     */
    public $timestamp;
    /**
     * @var mixed 结果集
     */
    public $result = '';
    /**
     * @var bool 是否回调函数
     */
    public $callback = false;
    /**
     * @var bool 是否script标签回调
     */
    public $callbackJavascriptTag = false;
    /**
     * @var string 字符编码
     */
    public $charset = 'UTF-8';

    /**
     * 错误结果
     * @param int $status
     * @param string $errmsg
     * @return $this
     */
    public function sendError($status=null, $errmsg=null)
    {
        return $this->sendResult('', $status, $errmsg);
    }

    /**
     * 发送结果
     * @param string $result
     * @param int $status
     * @param string $errmsg
     * @return $this
     */
    public function sendResult($result=null, $status=null, $errmsg=null) {
        $this->status = $status === null ? $this->status : $status;
        $this->result = $result === null ? $this->$result : $result;
        $this->errmsg = $errmsg === null ? $this->errmsg : $errmsg;
        $this->timestamp = time();
        $data = [
            'status' => $this->status,
            'result' => $this->result,
            'errmsg' => $this->errmsg,
            'timestamp' => $this->timestamp,
        ];
        $content = '';
        $contentType = 'application/json';
        $content = json_encode($data, JSON_UNESCAPED_UNICODE);
        if($this->callback)
        {
            $content = $this->callback . '(' . $content . ');';
            if($this->callbackJavascriptTag) {
                $content = '<script type="text/javascript">' . $content . '</script>';
            }
        } else {
            $this->setContentType($contentType, $this->charset);
        }
        $this->setContent($content);
        return $this;
    }
}