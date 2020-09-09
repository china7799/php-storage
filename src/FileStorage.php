<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace phpyii\storage;

use phpyii\storage\drivers\DriverAbstract;
use phpyii\storage\FileObject;
use phpyii\storage\FileResult;

/**
 * Description of FileStorage
 * 文件操作类
 * @author 最初的梦想
 */
class FileStorage {

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
     * @param array $driverConfig
     */
    public function setDriver(array $driverConfig = []) {
        switch ($driverConfig['type']) {
            case 'local':
                $this->driver = new drivers\LocalDriver($driverConfig['config']);
                break;
        }
    }
    
    /**
     * 处理文件
     * @param FileObject $fileObject
     */
    protected function dealFileObject(FileObject $fileObject) {
        $fileObject->setPath();
        $fileObject->fileUrl = trim($this->driver->getConfig('domain'), '/') . $this->saveFileUrl;
        return $fileObject;
    }
    

    /**
     * 保存文件
     * @param FileObject $fileObject
     * @return FileResult
     */
    public function save(FileObject $fileObject) {
        return $this->driver->save($this->dealFileObject($fileObject));
    }

    /**
     * 删除文件
     * @param FileObject $fileObject
     * @return FileResult
     */
    public function del(FileObject $fileObject) {
        return $this->driver->del($this->dealFileObject($fileObject));
    }

}
