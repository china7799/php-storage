<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace phpyii\storage\drivers;

use phpyii\storage\FileResult;

/**
 * Description of UpyunDriver
 * 又拍云存储
 * @author 最初的梦想
 */
class UpyunDriver extends DriverAbstract {

    /**
     * 配置
     * @var array 
     */
    protected $config = [
        'operator' => '',
        'password' => '',
        'bucket' => '',
        'domain' => '',
        'api_host' => 'v0.api.upyun.com',
        'use_ssl' => false,
    ];

    /**
     * 检测配置
     * @return bool
     * @throws \Exception
     */
    public function checkConfig(): bool {
        if (empty($this->config['operator']) || empty($this->config['password']) || empty($this->config['bucket']) || empty($this->config['api_host'])) {
            throw new \Exception("又拍云存储缺少配置参数");
        }
        $this->config['password'] = md5($this->config['password']);
        if (empty($this->config['domain'])) {
            $this->config['domain'] = $this->getScheme() . $this->config['bucket'] . '.b0.upaiyun.com';
        }
        return true;
    }

    /**
     * 保存文件
     * @return FileResult
     */
    public function save(): FileResult {
        $fr = FileResult::create();
        $beforeSave = $this->beforeSave();
        if (!$beforeSave['success']) {
            return $fr->setErrorMsg($beforeSave['msg']);
        }
        $filePath = '/' . $this->config['bucket'] . '/' . trim($this->fileObject->filePath, '/');
        $api = $this->getApi() . $filePath;
        $date = gmdate('D, d M Y H:i:s \G\M\T');
        $response = $this->request('PUT', $api, [
            'headers' => [
                'Date' => $date,
                'Authorization' => $this->getAuth('PUT', $filePath, $date),
                'Content-Type' => $this->fileObject->mime,
                'Content-Length' => $this->fileObject->size,
            ],
            'body' => $this->fileObject->fileData,
        ]);
        $statusCode = $response->getStatusCode();
        $fr->responseHeaders = $response->getHeaders();
        //xml结果处理simplexml_load_string
        $fr->responseBody = $response->getBody()->getContents();
        $fr->statusCode = $statusCode;
        if ($statusCode <> 200) {
            return $fr->setErrorMsg();
        }
        return $fr->setSuccessMsg();
    }

    /**
     * 删除
     * @param string $filePath
     * @return FileResult
     */
    public function del($filePath = ''): FileResult {
        $fr = FileResult::create();
        if (empty($filePath) && !empty($this->fileObject)) {
            $filePath = $this->fileObject->filePath;
        }
        $filePath = '/' . $this->config['bucket'] . '/' . trim($filePath, '/');
        $api = $this->getApi() . $filePath;
        $date = gmdate('D, d M Y H:i:s \G\M\T');
        $response = $this->request('DELETE', $api, [
            'headers' => [
                'Date' => $date,
                'Authorization' => $this->getAuth('DELETE', $filePath, $date),
            ]
        ]);
        $statusCode = $response->getStatusCode();
        $fr->responseHeaders = $response->getHeaders();
        //xml结果处理simplexml_load_string
        $fr->responseBody = $response->getBody()->getContents();
        $fr->statusCode = $statusCode;
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
        $response = $this->getMetadata($filePath);
        $statusCode = $response->getStatusCode();
        $fr->responseHeaders = $response->getHeaders();
        //xml结果处理simplexml_load_string
        $fr->responseBody = $response->getBody()->getContents();
        $fr->statusCode = $statusCode;
        if ($statusCode == 404) {
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
     * 获取api
     * @return string
     */
    private function getApi() {
        $apiHost = $this->config['api_host'];
        if (strpos($apiHost, 'http://') === 0) {
            return $apiHost;
        } elseif (strpos($apiHost, 'https://') === 0) {
            return $apiHost;
        } else {
            return $this->getScheme() . $apiHost;
        }
    }

    /**
     * 授权
     * @param type $method
     * @param type $uri
     * @param type $date
     * @param type $policy
     * @param type $md5
     * @return type
     */
    private function getAuth($method, $uri, $date, $policy = null, $md5 = null) {
        $elems = [];
        foreach ([$method, $uri, $date, $policy, $md5] as $v) {
            if ($v) {
                $elems[] = $v;
            }
        }
        $value = implode('&', $elems);
        $sign = base64_encode(hash_hmac('sha1', $value, $this->config['password'], true));
        return 'UPYUN ' . $this->config['operator'] . ':' . $sign;
    }
    
    /**
     * 文件metadata
     * @param string $filePath
     * @return ResponseInterface
     */
    private function getMetadata($filePath) {
        $filePath = '/' . $this->config['bucket'] . '/' . trim($filePath, '/');
        $api = $this->getApi() . $filePath;
        $date = gmdate('D, d M Y H:i:s \G\M\T');
        return $this->request('HEAD', $api, [
            'headers' => [
                'Date' => $date,
                'Authorization' => $this->getAuth('HEAD', $filePath, $date),
            ]
        ]);
    }
    
    
}
