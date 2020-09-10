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
        $savePath = $this->getConfig('save_path');
        $absoluteDir = trim($savePath, '/') . '/' . trim($fileObject->saveDir, '/') . '/';
        if (!is_dir($absoluteDir)) {
            mkdir($absoluteDir, 0777, true);
        }
        if (!is_writable($absoluteDir)) {
            throw new \Exception("Storage directory without permission!");
        }
        return true;
    }

    /**
     * 获取绝对路径
     * @param string $filePath
     * @return string
     */
    protected function getAbsolutePath($filePath) {
        $savePath = $this->getConfig('save_path');
        return trim($savePath, '/') . '/' . trim($filePath, '/');
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
        $absolutePath = $this->getAbsolutePath($fileObject->filePath);
        if (!$fileObject->isCover && is_file($absolutePath)) {
            return $fr->setErrorMsg('目标文件已经存在');
        }
        //保存文件
        if (!empty($fileObject->fileData)) {
            $file = fopen($absolutePath, "w+");
            if (!stream_copy_to_stream($fileObject->fileData, $file)) {
                fclose($file);
                return $fr->setErrorMsg();
            }
            fclose($file);
            return $fr->setSuccessMsg();
        } else if (!empty($fileObject->fileTmpPath)) {
            if (move_uploaded_file($fileObject->fileTmpPath, $absolutePath)) {
                return $fr->setSuccessMsg();
            } else {
                if (copy($fileObject->fileTmpPath, $absolutePath)) {
                    return $fr->setSuccessMsg();
                }
            }
        } else if (!empty($fileObject->fileBase64)) {
            $fileContent = base64_decode($fileObject->fileBase64);
            $handle = fopen($absolutePath, "w+");
            fwrite($handle, $fileContent);
            fclose($handle);
            return $fr->setSuccessMsg();
        }
        return $fr->setErrorMsg();
    }

    /**
     * 删除文件
     * @param string $filePath
     * @return mixed
     */
    public function del($filePath): FileResult {
        $fr = FileResult::create();
        $absolutePath = $this->getAbsolutePath($filePath);
        if (is_file($absolutePath) && @unlink($absolutePath)) {
            return $fr->setSuccessMsg('删除成功');
        }
        return $fr->setErrorMsg('删除失败');
    }
    
    /**
     * 文件是否存在
     * @param string $filePath
     * @return FileResult
     */
    public function has($filePath): bool {
        $absolutePath = $this->getAbsolutePath($filePath);
        if (is_file($absolutePath)) {
            return true;
        }
        return false;
    }

}
