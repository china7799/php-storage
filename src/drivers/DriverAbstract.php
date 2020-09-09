<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace phpyii\storage\drivers;

use phpyii\storage\FileObject;
use phpyii\storage\FileResult;

/**
 * Description of DriverAbstract
 * 驱动抽象类
 * @author 最初的梦想
 */
abstract class DriverAbstract {

    protected $config = [
        'domain' => '',
        'save_path' => ''
    ];

    /**
     * 构建函数
     * FilesInterface constructor.
     * @param $config
     */
    public function __construct(array $config = []) {
        if (!empty($config)) {
            $this->setConfig($config);
        }
    }

    /**
     * 设置配置
     * @param array $config
     */
    public function setConfig($config) {
        $config['save_path'] = rtrim(str_replace('\\', '/', $config['save_path']), '/');
        $this->config = array_merge($this->config, $config);
    }
    
    
    /**
     * 获取配置
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getConfig($key = null, $default = null) {
        if(empty($key)){
            return $this->config;
        }
        if(isset($this->config[$key])){
            return $this->config[$key];
        }
        return $default;
    }
    
    /**
     * 保存文件
     * @param FileObject $fileObject 文件对象
     * @return FileResult 上传结果
     */
    abstract public function save(FileObject $fileObject);

    /**
     * 删除文件
     * @param FileObject $fileObject 文件对象
     * @return FileResult
     */
    abstract public function del(FileObject $fileObject);
}
