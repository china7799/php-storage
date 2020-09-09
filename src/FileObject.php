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
    public $file_tmp_path = '';

    /**
     * 文件base64
     * @var string 
     */
    public $file_base64 = '';

    /**
     * 文件数据流
     * @var string 
     */
    public $file_data = null;

    /**
     * 目录
     * @var string 
     */
    public $dir = '';

    /**
     * 追加日期目录
     * @var bool 
     */
    public $dateDir = true;

    /**
     * 新文件名
     * @var string 
     */
    public $name = '';

    /**
     * 原文件名
     * @var string 
     */
    public $original_name = '';

    /**
     * 绝对路径
     * @var string 
     */
    public $absolute_path = '';

    /**
     * 相对路径
     * @var string 
     */
    public $relative_path = '';

    /**
     * 绝对路径目录
     * @var string 
     */
    public $absolute_dir = '';

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
     * md5
     * @var string 
     */
    public $md5 = '';

    /**
     * 文件sha1值
     * @var int 
     */
    public $sha1 = '';
    
    /**
     * 文件限制大小
     * @var int 
     */
    public $max_size = 1048576;
    
    /**
     * 允许的后缀
     * @var array 
     */
    public $allow_exts = [];


    /**
     * 设置路径
     * @return type
     */
    public function setPath($savePath = '') {
        if (empty($this->name)) {
            $this->createFileName();
        }
        if ($this->dateDir) {
            $year = date("Y");
            $day = date("md");
            $this->dir = trim($this->dir, '/') . '/' . $year . "/" . $day;
        }
        $this->relative_path = '/' . trim($this->dir, '/') . '/' . $this->name;
        $this->absolute_dir = trim($savePath, '/') . '/' . trim($this->dir, '/');
        $this->absolute_path = $this->absolute_dir . '/' . $this->name;
        return $this;
    }

    /**
     * 设置删除路径
     * @return type
     */
    public function setDelPath($savePath = '') {
        $this->absolute_path = trim($savePath, '/') . '/' . trim($this->relative_path, '/');
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
