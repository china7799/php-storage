<?php

declare (strict_types = 1);

namespace phpyii\storage\drivers;

use phpyii\storage\FileResult;

/**
 * Description of OssDriver
 * 阿里云存储
 * @author 最初的梦想
 */
class OssDriver extends DriverAbstract {
//    const OSS_HOST_TYPE_NORMAL = "normal"; //http://bucket.oss-cn-hangzhou.aliyuncs.com/object
//    const OSS_HOST_TYPE_IP = "ip";  //http://1.1.1.1/bucket/object
//    const OSS_HOST_TYPE_SPECIAL = 'special'; //http://bucket.guizhou.gov/object
//    const OSS_HOST_TYPE_CNAME = "cname";  //http://mydomain.com/object

    /**
     * 域名类型
     * @var string 
     */
    //private $hostType = self::OSS_HOST_TYPE_NORMAL;

    /**
     * 配置
     * @var array 
     */
    protected $config = [
        'secret_id' => '',
        'secret_key' => '',
        'bucket' => '',
        'region' => '', //endpoint oss-cn-hangzhou
        'domain' => '',
        'use_ssl' => false,
    ];

    /**
     * 检测配置
     * @return boolean
     * @throws \Exception
     */
    public function checkConfig(): bool {
        if (empty($this->config['secret_id']) || empty($this->config['secret_key']) || empty($this->config['bucket']) || empty($this->config['region'])) {
            throw new \Exception("阿里云存储缺少配置参数");
        }
        if (empty($this->config['domain'])) {
            $this->config['domain'] = $this->getScheme() . $this->getApiHost();
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
        $filePath = $this->fileObject->filePath = '/' . trim($this->fileObject->filePath, '/');
        $headers = [];
        if (!empty($this->fileObject->mime)) {
            $headers['Content-Type'] = $this->fileObject->mime;
        }
        $response = $this->request('PUT', $this->getApiUrl($filePath, 'PUT', $this->fileObject->mime), [
            'body' => $this->fileObject->fileData,
            'headers' => $headers
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
     * @throws \Exception
     */
    public function del($filePath = ''): FileResult {
        $fr = FileResult::create();
        if (empty($filePath) && !empty($this->fileObject)) {
            $filePath = $this->fileObject->filePath;
        }
        $filePath = '/' . trim($filePath, '/');
        $response = $this->request('DELETE', $this->getApiUrl($filePath, 'DELETE'));
        $statusCode = $response->getStatusCode();
        $fr->responseHeaders = $response->getHeaders();
        //xml结果处理simplexml_load_string
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
    public function has($filePath = ''): FileResult {
        $fr = FileResult::create();
        if (empty($filePath) && !empty($this->fileObject)) {
            $filePath = $this->fileObject->filePath;
        }
        $response = $this->getMetadata($filePath);
        $statusCode = $response->getStatusCode();
        $fr->responseHeaders = $response->getHeaders();
        $fr->responseBody = $response->getBody()->getContents();
        $fr->statusCode = $statusCode;
        if ($statusCode == 404) {
            return $fr->setErrorMsg('文件不存在');
        }
        return $fr->setSuccessMsg('文件存在');
//        if (in_array($statusCode, [401, 403])) {
//            return $fr->setErrorMsg('没有权限');
//        }
//        if (in_array($statusCode, [200, 304])) {
//            return $fr->setSuccessMsg('文件存在');
//        }
//        return $fr->setErrorMsg('文件不存在');
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
        $endpoint = $this->config['region'];
        $ret_endpoint = null;
        if (strpos($endpoint, 'http://') === 0) {
            $ret_endpoint = substr($endpoint, strlen('http://'));
        } elseif (strpos($endpoint, 'https://') === 0) {
            $ret_endpoint = substr($endpoint, strlen('https://'));
            $this->config['use_ssl'] = true;
        } else {
            if (strrchr($endpoint, '.com') == '.com') {
                $ret_endpoint = $this->getConfig('bucket') . '.' . $endpoint;
            } else {
                //bucket.oss-cn-hangzhou.aliyuncs.com
                $ret_endpoint = $this->getConfig('bucket') . '.' . $endpoint . '.' . 'aliyuncs.com';
            }
        }
        return $ret_endpoint;
    }

    /**
     * 获取请求地址
     * @param type $name
     * @param type $type
     * @param type $mime
     * @return type
     */
    private function getApiUrl($name, $type, $mime = '') {
        $time = time() + 1800;
        $policy = [];
        $policy[] = $type;
        $policy[] = '';
        if ($mime) {
            $policy[] = $mime;
        } else {
            $policy[] = '';
        }
        $policy[] = $time;
        $policy[] = '/' . $this->config['bucket'] . '/' . trim($name, '/');
        $policy = implode("\n", $policy);
        $signature = base64_encode(hash_hmac('sha1', $policy, $this->config['secret_key'], true));
        $data = [
            'OSSAccessKeyId' => $this->config['access_id'],
            'Expires' => $time,
            'Signature' => $signature,
        ];
        $api = $this->getScheme() . $this->getApiHost();
        return $api . '/' . trim($name, '/') . '?' . http_build_query($data);
    }

    /**
     * @param string $filePath
     * @return array|bool
     */
    private function getMetadata($filePath) {
        $filePath = '/' . trim($filePath, '/');
        return $this->request('HEAD', $this->getApiUrl($filePath, 'HEAD'));
    }

}
