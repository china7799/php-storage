<?php

declare (strict_types = 1);

namespace phpyii\storage\drivers;

use phpyii\storage\FileObject;
use phpyii\storage\FileResult;

/**
 * Description of LocalDriver
 * 本地上传
 * @author 最初的梦想
 */
class LocalDriver extends DriverAbstract {

    /**
     * 检查保存路径
     * @param FileObject $fileObject 文件对象
     * @return boolean
     * @throws \Exception
     */
    protected function checkPath(FileObject $fileObject) {
        $fileObject->setPath($this->getConfig('save_path', ''));
        $absolute_dir = $fileObject->absolute_dir;
        if (!is_dir($absolute_dir)) {
            mkdir($absolute_dir, 0777, true);
        }
        if (!is_writable($absolute_dir)) {
            throw new \Exception("Storage directory without permission!");
        }
        return true;
    }

    /**
     * 保存文件
     * @param FileObject $fileObject 文件对象
     */
    public function save(FileObject $fileObject): FileResult {
        $fr = FileResult::create();
        $fr->fileObject = $fileObject;
        if (!$this->checkPath($fileObject)) {
            return $fr->setErrorMsg('上传目录创建失败');
        }
        //保存文件
        if (!empty($fileObject->file_data)) {
            $file = fopen($fileObject->absolute_path, "w+");
            if (!stream_copy_to_stream($fileObject->file_data, $file)) {
                fclose($file);
                throw new \Exception("The file save failed!");
            }
            fclose($file);
            return $fr->setSuccessMsg('上传成功');
        } else if (!empty($fileObject->file_tmp_path)) {
            if (move_uploaded_file($fileObject->file_tmp_path, $fileObject->absolute_path)) {
                return $fr->setSuccessMsg('上传成功');
            }
            else{
                if(copy($fileObject->file_tmp_path, $fileObject->absolute_path)){
                    return $fr->setSuccessMsg('上传成功');
                }
            }
        } else if (!empty($fileObject->file_base64)) {
            
        }
        return $fr->setErrorMsg('上传失败');
    }

    /**
     * 删除文件
     * @param FileObject $fileObject
     * @return mixed
     */
    public function del(FileObject $fileObject): FileResult {
        $fileObject->setDelPath($this->getConfig('save_path', ''));
        $absolute_path = $fileObject->absolute_path;
        if (is_file($absolute_path) && @unlink($absolute_path)) {
            return FileResult::create(true, '删除成功');
        }
        return FileResult::create(false, '删除失败');
    }

}
