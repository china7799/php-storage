<?php

declare (strict_types = 1);

namespace phpyii\storage\drivers;

use phpyii\storage\FileObject;
use phpyii\storage\FileResult;

/**
 * Description of CosDriver
 * 腾讯云存储
 * @author 最初的梦想
 */
class CosDriver extends DriverAbstract {

    /**
     * 配置
     * @var array 
     */
    protected $config = [
        'secret_id' => '',
        'secret_key' => '',
        'app_id' => '',
        'bucket' => '',
        'region' => '',
        'domain' => '',
        'use_ssl' => false,
    ];

    /**
     * 检测配置
     * @return boolean
     * @throws \Exception
     */
    public function checkConfig() {
        if (empty($this->config['secret_id']) || empty($this->config['secret_key']) || empty($this->config['bucket']) || empty($this->config['url'])) {
            throw new \Exception("缺少配置参数");
        }
        if (empty($this->config['domain'])) {
            $this->config['domain'] = $this->getScheme() . $this->getApiHost();
        }
        return true;
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
     * 获取host
     * @return string
     */
    private function getApiHost() {
        $host = $this->getConfig('bucket') . '-' . $this->getConfig('app_id') . '.cos.' . $this->getConfig('region') . '.myqcloud.com';
        return $host;
    }

    /**
     * 上传文件
     * @return FileResult
     * @throws \Exception
     */
    public function save(): FileResult {
        $fileObject = $this->fileObject;
        $fr = FileResult::create();
        $filePath = $fileObject->filePath = '/' . trim($fileObject->filePath, '/');
        if (!$fileObject->isCover) {
            if ($this->has($filePath)) {
                return $fr->setErrorMsg('目标文件已经存在');
            }
        }
        if (empty($fileObject->fileData)) {
            if (!empty($fileObject->fileTmpPath)) {
                $fileObject->fileData = file_get_contents($fileObject->fileTmpPath);
            }
            if (!empty($fileObject->fileBase64)) {
                $fileObject->fileData = base64_decode($fileObject->fileBase64);
            }
        }
        if (empty($fileObject->fileData)) {
            return $fr->setErrorMsg('上传的文件不存在');
        }
        if (empty($fileObject->size)) {
            $fileObject->size = strlen($fileObject->fileData);
        }
        $headers = [];
        if (!empty($fileObject->mime)) {
            $headers['Content-Type'] = $fileObject->mime;
        }
        if (!empty($fileObject->size)) {
            $headers['Content-Length'] = $fileObject->size;
        }
        $auth = $this->getAuth($filePath, 'PUT', [], $headers);
        $headers['Authorization'] = $auth;
        //请求地址
        $api = $this->getScheme() . $this->getApiHost() . $filePath;
        $response = $this->request('PUT', $api, [
            'body' => $fileObject->fileData,
            'headers' => $headers
        ]);
        $statusCode = $response->getStatusCode();
        $fr->responseHeaders = $response->getHeaders();
        $fr->responseBody = $response->getBody()->getContents();
        if ($statusCode <> 200) {
            return $fr->setErrorMsg();
        }
        return $fr->setSuccessMsg();
    }

    /**
     * 删除文件
     * @param string $filePath
     * @return bool
     */
    public function del($filePath = ''): FileResult {
        $fr = FileResult::create();
        if(empty($filePath) && !empty($this->fileObject)){
            $filePath = $this->fileObject->filePath;
        }
        $filePath = '/' . trim($filePath, '/');
        $auth = $this->getAuth($filePath, 'DELETE');
        //请求地址
        $api = $this->getScheme() . $this->getApiHost() . $filePath;
        $response = $this->request('DELETE', $api, [
            'headers' => [
                'Authorization' => $auth
            ]
        ]);
        $statusCode = $response->getStatusCode();
        $fr->responseHeaders = $response->getHeaders();
        $fr->responseBody = $response->getBody()->getContents();
        if ($statusCode <> 204) {
            return $fr->setErrorMsg('删除失败');
        }
        return $fr->setSuccessMsg('删除成功');
    }

    /**
     * 文件是否存在
     * @param string $filePath
     * @return bool
     */
    public function has($filePath = ''): bool {
        if(empty($filePath) && !empty($this->fileObject)){
            $filePath = $this->fileObject->filePath;
        }
        $response = $this->getMetadata($filePath);
        $statusCode = $response->getStatusCode();
        if($statusCode == 403){
            throw new \Exception('没有权限');
        }
        if (in_array($statusCode, [200, 304])) {
            return true;
        }
        return false;
    }

    /**
     * 认证
     * @param type $name
     * @param type $type
     * @param type $query
     * @param type $header
     * @return type
     */
    private function getAuth($name, $type, $query = [], $header = []) {
        $time = time();
        $expiredTime = $time + 1800;
        $keyTime = $time . ';' . $expiredTime;
        $signKey = hash_hmac("sha1", $keyTime, $this->config['secret_key']);
        $httpString = implode("\n", [strtolower($type), $name, $this->httpParameters($query), $this->httpParameters($header), '']);
        $stringToSign = implode("\n", ['sha1', $keyTime, sha1($httpString), '']);
        $signature = hash_hmac('sha1', $stringToSign, $signKey);
        $data = [];
        $data['q-sign-algorithm'] = 'sha1';
        $data['q-ak'] = $this->config['secret_id'];
        $data['q-sign-time'] = $keyTime;
        $data['q-key-time'] = $keyTime;
        $data['q-header-list'] = $this->urlParamList($header);
        $data['q-url-param-list'] = $this->urlParamList($query);
        $data['q-signature'] = $signature;
        $sign = [];
        foreach ($data as $key => $vo) {
            $sign[] = $key . '=' . $vo;
        }
        return implode('&', $sign);
    }

    private function urlParamList($data) {
        $list = array_keys($data);
        sort($list);
        $list = array_map(function ($vo) {
            return urlencode($vo);
        }, $list);
        return strtolower(implode(';', $list));
    }

    private function httpParameters($data) {
        $keys = array_keys($data);
        sort($keys);
        $data = array_merge(array_flip($keys), $data);
        $tmp = [];
        foreach ($data as $key => $vo) {
            $tmp[strtolower($key)] = $vo;
        }
        return http_build_query($tmp);
    }

    /**
     * @param string $filePath
     * @return array|bool
     */
    private function getMetadata($filePath) {
        $filePath = '/' . trim($filePath, '/');
        $auth = $this->getAuth($filePath, 'HEAD');
        //请求地址
        $api = $this->getScheme() . $this->getApiHost() . $filePath;
        return $this->request('HEAD', $api, [
                    'headers' => [
                        'Authorization' => $auth
                    ]
        ]);
    }

}
