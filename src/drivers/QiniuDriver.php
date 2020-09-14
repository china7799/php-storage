<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace phpyii\storage\drivers;

use phpyii\storage\FileResult;

/**
 * Description of DriverAbstract
 * 七牛存储
 * @author 最初的梦想
 */
class QiniuDriver extends DriverAbstract {

    protected $config = [
        'access_key' => '',
        'secret_key' => '',
        'bucket' => '',
        'domain' => '', //空间绑定的域名
        'api_host' => 'http://up.qiniup.com', //上传域名
        'use_ssl' => false,
    ];

    /**
     * 检测配置
     * @return boolean
     * @throws \Exception
     */
    public function checkConfig(): bool {
        if (empty($this->config['access_key']) || empty($this->config['secret_key']) || empty($this->config['bucket']) || empty($this->config['domain']) || empty($this->config['api_host'])) {
            throw new \Exception("七牛云存储缺少配置参数");
        }
        return true;
    }

    /**
     * 文件上传
     * @return FileResult
     */
    public function save(): FileResult {
        $fr = FileResult::create();
        $beforeSave = $this->beforeSave();
        if (!$beforeSave['success']) {
            return $fr->setErrorMsg($beforeSave['msg']);
        }
        $filePath =  trim($this->fileObject->filePath, '/');
        $auth = $this->getSign();
        $response = $this->request('POST', $this->config['api_host'], [
            'multipart' => [
                    [
                    'name' => 'token',
                    'contents' => $auth
                ],
                    [
                    'name' => 'key',
                    'contents' => $filePath
                ],
                    [
                    'name' => 'file',
                    'contents' => $this->fileObject->fileData
                ],
            ],
        ]);
        $statusCode = $response->getStatusCode();
        $fr->responseHeaders = $response->getHeaders();
        //xml结果处理simplexml_load_string
        $fr->responseBody = $response->getBody()->getContents();
        if ($statusCode <> 200) {
            return $fr->setErrorMsg();
        }
        return $fr->setSuccessMsg();
    }

    /**
     * 删除文件
     * @param string $filePath
     * @return FileResult
     */
    public function del($filePath = ''): FileResult {
        $fr = FileResult::create();
        if (empty($filePath) && !empty($this->fileObject)) {
            $filePath = $this->fileObject->filePath;
        }
        $auth = $this->getAuth('delete', $filePath);
        $api = $this->getScheme() . 'rs.qiniu.com';
        $response = $this->request('POST', $api. $auth['path'],  [
            'headers' => [
                'Authorization' => $auth['auth']
            ],
        ]);
        $statusCode = $response->getStatusCode();
        $fr->responseHeaders = $response->getHeaders();
        //xml结果处理simplexml_load_string
        $fr->responseBody = $response->getBody()->getContents();
        if ($statusCode <> 200) {
            return $fr->setErrorMsg('删除失败');
        }
        return $fr->setSuccessMsg('删除成功');
    }

    /**
     * 文件是否存在
     * @param string $filePath
     * @return FileResult
     */
    public function has($filePath = ''): FileResult {
        $fr = FileResult::create();
        if (empty($filePath) && !empty($this->fileObject)) {
            $filePath = $this->fileObject->filePath;
        }
        $auth = $this->getAuth('stat', $filePath);
        $api = $this->getScheme() . 'rs.qiniu.com';
        $response = $this->request('GET', $api. $auth['path'],  [
            'headers' => [
                'Authorization' => $auth['auth']
            ],
        ]);
        $statusCode = $response->getStatusCode();
        $fr->responseHeaders = $response->getHeaders();
        //xml结果处理simplexml_load_string
        $fr->responseBody = $response->getBody()->getContents();
        $fr->statusCode = $statusCode;
        if ($statusCode == 612) {
            return $fr->setErrorMsg('文件不存在');
        }
        return $fr->setSuccessMsg('文件存在');
    }

    /**
     * 协议头
     * @return string
     */
    private function getScheme() {
        $scheme = 'http://';
        if ($this->getConfig('use_ssl')) {
            $scheme = 'https://';
        }
        return $scheme;
    }

    /**
     * 授权
     * @param type $type
     * @param type $filePath
     * @return type
     */
    private function getAuth($type, $filePath) {
        $filePath = trim($filePath, '/');
        $entry = $this->encode($this->config['bucket'] . ':' . $filePath);
        $path = '/' . $type . '/' . $entry;
        $sign = $this->encode(hash_hmac('sha1', $path . "\n", $this->config['secret_key'], true));
        $auth = 'QBox ' . $this->config['access_key'] . ':' . $sign;
        return [
            'auth' => $auth,
            'path' => $path
        ];
    }

    /**
     * 签名
     * @return type
     */
    private function getSign() {
        $time = time() + 1800;
        $data = ['scope' => $this->config['bucket'], 'deadline' => $time];
        $data = $this->encode(json_encode($data));
        return $this->sign($this->config['secret_key'], $this->config['access_key'], $data) . ':' . $data;
    }

    private function encode($str) {
        return str_replace(['+', '/'], ['-', '_'], base64_encode($str));
    }

    private function sign($sk, $ak, $data) {
        $sign = hash_hmac('sha1', $data, $sk, true);
        return $ak . ':' . $this->encode($sign);
    }

}
