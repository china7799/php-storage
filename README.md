# php-storage
文件上传

安装
------------

```
composer require phpyii/php-storage

//开发版本
composer require phpyii/php-storage:dev-master

```

# 简介

php-storage PHP多存储驱动的文件管理类，支持多种云存储平台。

# 支持平台

- 本地服务器
- 腾讯云 COS
- 阿里云 OSS
- 七牛云存储 (未实现)
- 又拍云存储 (未实现)

# 使用方法

   ```php
    $file = new FileObject();

    //本地存储
    $file->setDriver([
        'type' => 'local',
        'config' => [
            'domain' => 'http://www.baidu.com',
            'save_path' => 'E:/',
        ],
    ]);

    //腾讯cos
    //$file->setDriver([
    //    'type' => 'cos',
    //    'config' => [
    //        'secret_id' => '',
    //        'secret_key' => '',
    //        'app_id' => '',
    //        'bucket' => '',
    //        'region' => 'ap-beijing',
    //        'domain' => '', //留空自动设置
    //        'use_ssl' => false,
    //    ],
    //]);

    //阿里oss
    //$file->setDriver([
    //    'type' => 'oss',
    //    'config' => [
    //        'secret_id' => '',
    //        'secret_key' => '',
    //        'bucket' => '',
    //        'region' => 'oss-cn-hangzhou',
    //        'domain' => '', //留空自动设置
    //        'use_ssl' => false,
    //    ],
    //]);

    //七牛
    //$file->setDriver([
    //    'type' => 'qiniu',
    //    'config' => [
    //        'access_key' => '',
    //        'secret_key' => '',
    //        'bucket' => '',
    //        'domain' => 'http://www.baidu.com',
    //        'api_host' => 'http://up.qiniup.com',
    //        'use_ssl' => false,
    //    ],
    //]);


    //其他继承了DriverAbstract类的所有自定义类都可以
    //$customClass = new CustomClass();
    //$file->setDriver(customClass);

    //上传
    //$file->fileTmpPath = 'E:/a.jpg';
    ////$file->filePath = ''; //带后缀的新文件名称  留空自动生成
    ////$fileObject->fileBase64 = ''; //图片base64字符串 data:image/png;base64,后边的字符串
    //$file->ext = '.jpg'; //自动生成新名称时用
    ////$file->mime = 'image/jpeg'; //留空通过后缀自动获取
    //$file->saveDir = 'dev/test'; //保存目录
    //$file->dateDir = true; //是否自动追加日期目录
    //$file->isCover = true; //是否覆盖
    //$bool = $file->save();
    //if($bool){
    //    //文件保存路径
    //    //$file->filePath;
    //    //文件访问路径
    //    //$file->fileUrl;
    //}
    // else{
    //    var_dump($file->getMsg());
    // }

    //删除
    //$bool = $file->del('/dev/test/2020/0910/08e87202009101612179540.jpg');
    //或者
    //$file->filePath = '/dev/test/2020/0910/08e87202009101612179540.jpg';
    //$bool = $file->del();

    //文件是否存在
    $bool = $file->has('/dev/test/2020/0910/0e80b202009101613326313.jpg');
    //或者
    //$file->filePath = '/dev/test/2020/0910/08e87202009101612179540.jpg';
    //$bool = $file->has();

    //操作结果
    var_dump($bool);
    //文件路径
    var_dump($file->filePath);
    //带域名文件路径
    var_dump($file->fileUrl);
    //第三方存储请求结果
    var_dump($file->getResult());

   ```
