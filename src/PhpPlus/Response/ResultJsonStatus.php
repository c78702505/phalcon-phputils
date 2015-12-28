<?php

namespace PhpPlus\Response;

/**
 * 状态值
 * @package PhpPlus\Response
 */
class ResultJsonStatus
{
    /**
     * 成功
     */
    const SUCCESS = 10000;
    /**
     * 权限不足
     */
    const RIGHT_VERIFY_FAIL = -10000;
    /**
     * 参数不全
     */
    const PARAM_CANNOT_EMPTY = 100;
    /**
     * 用户授权失效
     */
    const USER_TOKEN_EXPIRES = 200;

    /**
     * 参数异常
     */
    const PARAM_ERROR = 300;

    /**
     * 数据库异常
     */
    const DATABASE_ERROR = 400;
    /**
     * 上传文件处理异常
     */
    const UPLOAD_FILE_ERROR = 401;
    /**
     * 业务异常
     */
    const BUSINESS = 999;
    /**
     * 未知错误
     */
    const ERROR = 9999;
}