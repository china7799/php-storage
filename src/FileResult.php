<?php

declare (strict_types = 1);

namespace phpyii\storage;


/**
 * Description of UploadResult
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
     * 文件对象
     * @var FileObject 
     */
    public $fileObject;


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
     * @param FileObject $fileObject
     * @return $this
     */
    public function setSuccessMsg($msg = '上传成功', FileObject $fileObject = null) {
        $this->msg = $msg;
        if(!empty($fileObject)){
            $this->fileObject = $fileObject;
        }
        $this->success = true;
        return $this;
    }
    
    /**
     * 设置错误消息
     * @param string $msg
     * @return $this
     */
    public function setErrorMsg($msg = '上传失败') {
        $this->msg = $msg;
        $this->success = false;
        return $this;
    }
    
}
