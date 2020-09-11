<?php

declare (strict_types = 1);

namespace phpyii\storage;


/**
 * Description of FileResult
 * 上传或保存结果
 * @author 最初的梦想
 */
class FileResult {
    
    /**
     * 是否成功
     * @var bool 
     */
    public $success = false;
    
    /**
     * 消息
     * @var string 
     */
    public $msg = '';

    /**
     * 第三方http状态码
     * @var int 
     */
    public $statusCode;


    /**
     * 第三方响应头
     * @var string 
     */
    public $responseHeaders = [];
    
    
    /**
     * 第三方响应消息
     * @var string 
     */
    public $responseBody = '';
    

    /**
     * 创建对象
     * @param bool $success
     * @param string $msg
     * @return \self
     */
    public static function create(bool $success = true, string $msg = ''){
        $result = new self();
        $result->success = $success;
        $result->msg = $msg;
        return $result;
    }
    
    /**
     * 设置成功消息
     * @param string $msg
     * @return $this
     */
    public function setSuccessMsg($msg = '上传成功') {
        $this->success = true;
        $this->msg = $msg;
        return $this;
    }
    
    /**
     * 设置错误消息
     * @param string $msg
     * @return $this
     */
    public function setErrorMsg($msg = '上传失败') {
        $this->success = false;
        $this->msg = $msg;
        return $this;
    }
    
}
