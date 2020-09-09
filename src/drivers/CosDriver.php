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
        if(empty($this->config['domain'])){
            $this->config['domain'] = $this->getScheme() . $this->getApiHost();
        }
        return true;
    }

    /**
     * 协议头
     * @return string
     */
    private function getScheme(){
        $scheme = 'http://';
        if($this->getConfig('use_ssl')){
            $scheme = 'https://';
        }
        return $scheme;
    }

        /**
     * 获取host
     * @return string
     */
    private function getApiHost() {
        $host = $this->getConfig('bucket') . '-' . $this->getConfig('app_id') . '.cos.' . $this->getConfig('region')  .'.myqcloud.com';
        return $host;
    }

    /**
     * 
     * @param  FileObject $fileObject
     * @return FileResult
     * @throws \Exception
     */
    public function save(FileObject $fileObject): FileResult {
        $fr = FileResult::create();
        $fr->fileObject = $fileObject;
        if (empty($fileObject->fileData)) {
            if (!empty($fileObject->fileTmpPath)) {
                $fileObject->fileData = file_get_contents($fileObject->fileTmpPath);
            }
            if (!empty($fileObject->fileBase64)) {
                $fileObject->fileData = base64_decode($fileObject->fileBase64);
            }
        }
        if(empty($fileObject->size)){
            $fileObject->size = strlen($fileObject->fileData);
        }
        $file = $fileObject->saveFileUrl;
        $headers = [];
        if (!empty($fileObject->mime)) {
            $headers['Content-Type'] = $fileObject->mime;
        }
        if (!empty($fileObject->size)) {
            $headers['Content-Length'] = $fileObject->size;
        }
        $auth = $this->getAuth($file, 'PUT', [], $headers);
        $headers['Authorization'] = $auth;
        //请求地址
        $api = $this->getScheme() . $this->getApiHost(). '/' . trim($file, '/');
        $response = (new \GuzzleHttp\Client())->request('PUT', $api, [
            'body' => $fileObject->fileData,
            'headers' => $headers
        ]);
        $reason = $response->getStatusCode();
        $fr->responseHeaders = $response->getHeaders();
        $fr->responseBody = $response->getBody()->getContents();
        if ($reason <> 200) {
            return $fr->setErrorMsg();
        }
        return $fr->setSuccessMsg();
    }

    /**
     * 删除文件
     * @param FileObject $fileObject
     */
    public function del(FileObject $fileObject): FileResult {
        $fr = FileResult::create();
        $fr->fileObject = $fileObject;
        $auth = $this->getAuth($fileObject->saveFileUrl, 'DELETE');
        //请求地址
        $api = $this->getScheme() . $this->getApiHost(). '/' . trim($fileObject->saveFileUrl, '/');
        $response = (new \GuzzleHttp\Client())->request('DELETE', $api, [
            'headers' => [
                'Authorization' => $auth
            ]
        ]);
        $reason = $response->getStatusCode();
        $fr->responseHeaders = $response->getHeaders();
        $fr->responseBody = $response->getBody()->getContents();
        if ($reason <> 204) {
            return $fr->setErrorMsg('删除失败');
        }
        return $fr->setSuccessMsg('删除成功');
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

}
