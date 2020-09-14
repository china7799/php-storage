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
        //文件类型
        if(empty($this->mime) && !empty($this->name)){
            $this->mime = MimeTypes::getMimetype($this->name);
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
        return $this;
    }

    /**
     * 随机生成文件名
     */
    protected function createFileName() {
        $name = substr(md5(uniqid()), 0, 5) . date('YmdHis') . rand(0, 9999);
        $this->name = strtolower($name . trim($this->ext));
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
