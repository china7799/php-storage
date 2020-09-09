# php-storage
文件上传

安装
------------

```
composer require phpyii/php-storage

```

# 简介

php-storage PHP多存储驱动的文件管理类，支持多种云存储平台。

# 支持平台

- 本地服务器
- 腾讯云 COS
- 阿里云 OSS (未实现)
- 七牛云存储 (未实现)
- 又拍云存储 (未实现)

# 使用方法

   ```php
    //本地
    $fileStorage = new FileStorage();
    $fileStorage->setDriver([
        'type' => 'local',
        'config' => [
            'domain' => 'http://www.baidu.com',
            'save_path' => 'E:/',
        ],
    ]);
    $fileObject = new FileObject();
    $fileObject->fileTmpPath = 'E:/test.php';
    $fileObject->ext = '.php';

    //腾讯cos
    $fileStorage = new FileStorage();
    $fileStorage->setDriver([
        'type' => 'cos',
        'config' => [
            'secret_id' => '',
            'secret_key' => '',
            'app_id' => '',
            'bucket' => '',
            'region' => 'ap-beijing',
            'domain' => '',
        ],
    ]);
    $fileObject = new FileObject();
    $fileObject->fileTmpPath = 'E:/a.jpg';
    $fileObject->ext = '.jpg';
    $fileObject->mime = 'image/jpeg';
    $fileObject->saveDir = 'dev/test';

    //上传
    $fr = $fileStorage->save($fileObject);
    if($fr->success){
        //文件保存路径
        //$fr->fileObject->saveFileUrl;
        //文件访问路径
        //$fr->fileObject->fileUrl;
    }
    var_dump($fr);

    //删除
    $fileObject->saveFileUrl = '/dev/test/2020/0909/13d8320200909171303757.jpg';
    $fr = $fileStorage->del($fileObject);
    var_dump($fr);


   ```
