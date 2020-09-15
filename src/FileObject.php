<?php

declare (strict_types = 1);

namespace phpyii\storage;

use phpyii\storage\MimeTypes;
use phpyii\storage\drivers\DriverAbstract;

/**
 * Description of FileObject
 * 文件对象
 * @author 最初的梦想
 */
class FileObject {

    /**
     * 文件临时路径
     * @var string 
     */
    public $fileTmpPath = '';

    /**
     * 文件base64
     * @var string 
     */
    public $fileBase64 = '';

    /**
     * 文件数据流
     * @var string 
     */
    public $fileData = null;

    /**
     * 保存相对目录
     * @var string 
     */
    public $saveDir = '';

    /**
     * 是否创建日期目录
     * @var bool 
     */
    public $dateDir = true;

    /**
     * 原文件名
     * @var string 
     */
    public $oldName = '';
    
    /**
     * 文件名
     * @var string 
     */
    public $name = '';

    /**
     * 数据库保存的文件地址
     * @var string 
     */
    public $filePath = '';

    /**
     * 文件访问地址
     * @var string 
     */
    public $fileUrl = '';

    /**
     * 后缀
     * @var string 
     */
    public $ext = '';
    
    /**
     * 文件大小
     * @var int 
     */
    public $size = 0;    
    
    /**
     * 文件类型
     * @var type 
     */
    public $mime = '';

    /**
     * 文件存在是否覆盖
     * @var bool 
     */
    public $isCover = false;

    /**
     * 文件限制大小
     * @var int 
     */
    public $maxSize = 1048576;

    /**
     * 允许的后缀
     * @var array 
     */
    public $allowExts = [];

    /**
     * 结果
     * @var FileResult 
     */
    protected $fileResult = null;
    
    
    /**
     * 文件驱动
     * @var DriverAbstract 
     */
    protected $driver = null;

    /**
     * 构建函数
     * @param array $driverConfig
     */
    public function __construct(array $driverConfig = []) {
        if (!empty($driverConfig)) {
            $this->setDriver($driverConfig);
        }
    }
    
    /**
     * 设置驱动
     * @param array|DriverAbstract $driver
     */
    public function setDriver($driver = []) {
        if($driver instanceof DriverAbstract){
            $this->driver = $driver;
        }
        else{
            $driverClass = 'phpyii\storage\drivers\\'.ucfirst($driver['type']).'Driver';
            $this->driver = new $driverClass($driver['config']);
        }
    }
    
    
    /**
     * 处理文件
     */
    protected function dealFileObject() {
        //把文件路径或者base字符串转为二进制文件内容
        if (empty($this->fileData)) {
            if (!empty($this->fileTmpPath)) {
                $this->fileData = file_get_contents($this->fileTmpPath);
            }
            if (!empty($this->fileBase64)) {
                $this->fileData = base64_decode($this->fileBase64);
                //清空base64 释放内存
                $this->fileBase64 = '';
            }
        }
        if (empty($this->ext) && $this->fileObject->ext !== false) {
            //通过文件名识别后缀
            if(!empty($this->oldName)){
                $ext = pathinfo($this->oldName, PATHINFO_EXTENSION);
                if(!empty($ext)){
                    $this->ext = $ext;
                }
            }
            //通过文件内容识别后缀
            if(empty($this->ext) && !empty($this->fileData)){
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->buffer($this->fileData);
                if (!empty($mime)) {
                    $this->mime = $mime;
                    $ext = MimeTypes::getExtension($mime);
                    $this->ext = $ext;
                }
                unset($finfo);
            }
        }
        if (empty($this->filePath)) {
            if (empty($this->name)) {
                $this->createFileName();
            }
            $dir = trim($this->saveDir, '/');
            $dateDir = '';
            if ($this->dateDir) {
                $year = date("Y");
                $day = date("md");
                $dateDir = '/' . $year . "/" . $day . '/';
            }
            $dir = $dir . $dateDir;
            if (empty($dir)) {
                $this->filePath = '/' . trim($this->name, '/');
            } else {
                $this->saveDir = $dir;
                $this->filePath = '/' . trim($this->saveDir, '/') . '/' . trim($this->name, '/');
            }
        }
        $this->fileUrl = trim($this->driver->getConfig('domain'), '/') . '/' . trim($this->filePath, '/');
        //通过文件后缀识别文件类型
        if(empty($this->mime)){
            $mime = '';
            if(!empty($this->oldName)){
                $mime = MimeTypes::getMimetypeByName($this->oldName);
            }
            else if(!empty($this->ext)){
                $mime = MimeTypes::getMimetypeByName($this->ext);
            }
            if(!empty($mime)){
                $this->mime = $mime;
            }
        }
//        if (empty($this->fileData)) {
//            if (!empty($this->fileTmpPath)) {
//                $this->fileData = file_get_contents($this->fileTmpPath);
//            }
//            if (!empty($this->fileBase64)) {
//                $this->fileData = base64_decode($this->fileBase64);
//            }
//        }
        if (empty($this->size) && !empty($this->fileData)) {
            $this->size = strlen($this->fileData);
        }
        $this->ext = strtolower(trim($this->ext));
        $this->mime = strtolower(trim($this->mime));
        return $this;
    }

    /**
     * 随机生成文件名
     */
    protected function createFileName() {
        $name = substr(md5(uniqid()), 0, 5) . date('YmdHis') . rand(0, 9999);
        $this->name = strtolower($name);
        if(!empty($this->ext)){
            $this->name = $this->name . '.' . trim($this->ext, '.');
        }
    }

    
    /**
     * 调用方法
     * @param type $method
     * @param type $arguments
     * @return type
     */
    public function __call($method, $arguments) {
        $this->driver->setFileObject($this->dealFileObject());
        $result = call_user_func_array(array($this->driver, $method), $arguments);
        if($result instanceof FileResult){
            $this->fileResult = $result;
            return $this->fileResult->success;
        }
        return $result;
    }
    
    /**
     * 获取结果
     * @return type
     */
    public function getResult() {
        return $this->fileResult;
    }
    
    /**
     * 获取结果
     * @return type
     */
    public function getMsg() {
        if(empty($this->fileResult)){
            return '';
        }
        return $this->fileResult->msg;
    }
    
    
}
