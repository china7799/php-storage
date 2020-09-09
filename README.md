# php-storage
文件上传

# 简介

php-storage PHP多存储驱动的文件管理类，支持多种云存储平台。

# 支持平台

- 本地服务器
- 阿里云 OSS (未实现)
- 腾讯云 COS (未实现) 
- 七牛云存储 (未实现)
- 又拍云存储 (未实现)

# 使用方法

   ```php
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
    $fr = $fileStorage->save($fileObject);
    if($fr->success){
        //文件保存路径
        //$fr->fileObject->saveFileUrl;
        //文件访问路径
        //$fr->fileObject->fileUrl;
    }
    var_dump($fr);
   ```
