<?php

declare (strict_types = 1);

namespace phpyii\storage\drivers;

use phpyii\storage\FileObject;
use phpyii\storage\FileResult;

/**
 * Description of DriverAbstract
 * 存储抽象类
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
        if (empty($key)) {
            return $this->config;
        }
        if (isset($this->config[$key])) {
            return $this->config[$key];
        }
        return $default;
    }

    /**
     * 封装GuzzleHttp请求
     * @param type $method
     * @param type $uri
     * @param array $options
     * @return type
     */
    public function request($method, $uri = '', array $options = []) {
        try {
            return (new \GuzzleHttp\Client())->request($method, $uri, $options);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            if ($e->hasResponse()) {
                return $e->getResponse();
            }
        } catch (\Exception $exc) {
            return false;
        }
        return false;
    }

    /**
     * 保存文件
     * @param FileObject $fileObject 文件对象
     * @return FileResult 上传结果
     */
    abstract public function save(FileObject $fileObject);

    /**
     * 删除文件
     * @param string $filePath  文件路径
     * @return FileResult
     */
    abstract public function del($filePath);

    /**
     * 文件是否存在
     * @param string $filePath 文件路径
     * @return bool
     */
    abstract public function has($filePath);
}
