<?php

declare (strict_types = 1);

namespace phpyii\storage;

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
    public $saveFileUrl = '';

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
     * 设置路径
     * @return $this
     */
    public function setPath() {
        if (empty($this->name)) {
            $this->createFileName();
        }
        if (empty($this->saveFileUr)) {
            $dir = trim($this->saveDir, '/');
            $dateDir = '';
            if ($this->dateDir) {
                $year = date("Y");
                $day = date("md");
                $dateDir = '/' . $year . "/" . $day . '/';
            }
            $dir = $dir . $dateDir;
            if (empty($dir)) {
                $this->saveFileUrl = '/' . trim($this->name, '/');
            } else {
                $this->saveDir = $dir;
                $this->saveFileUrl = '/' . trim($this->saveDir, '/') . '/' . trim($this->name, '/');
            }
        }
        return $this;
    }

    /**
     * 随机生成文件名
     */
    public function createFileName() {
        $name = substr(md5(uniqid()), 0, 5) . date('YmdHis') . rand(0, 9999);
        $this->name = strtolower($name . trim($this->ext));
    }

}
