# php-storage
文件上传

# 简介

php-storage PHP多存储驱动的文件管理类，支持多种云存储平台。

# 支持平台

- 本地服务器
- 阿里云 OSS
- 腾讯云 COS
- 七牛云存储
- 又拍云存储

# 使用方法

   ```
    $fileStorage = new FileStorage();
    $fileStorage->setDriver([
        'type' => 'local',
        'config' => [
            'save_path' => 'E:/',
        ],
    ]);
    $fileObject = new FileObject();
    $fileObject->file_tmp_path = 'E:/test.php';
    $fileObject->ext = '.php';
    $fr = $fileStorage->save($fileObject);
    if($fr->success){
        //成功
    }
    var_dump($fr);
   ```
