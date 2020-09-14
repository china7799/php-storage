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
     * 设置文件 覆盖父类
     * @param FileObject $fileObject
     */
    public function setFileObject(FileObject $fileObject) {
        $this->fileObject = $fileObject;
        //计算文件大小
        if (empty($this->fileObject->size)) {
            if (!empty($this->fileObject->fileData)) {
                $this->fileObject->size = strlen($this->fileObject->fileData);
            } else if (!empty($this->fileObject->fileTmpPath)) {
                $this->fileObject->size = filesize($this->fileObject->fileTmpPath);
            } else if (!empty($this->fileObject->fileBase64)) {
                $this->fileObject->size = strlen($this->fileObject->fileBase64);
            }
        }
    }

    /**
     * 检测配置
     * @return boolean
     * @throws \Exception
     */
    public function checkConfig(): bool {
        if (empty($this->config['save_path']) || empty($this->config['domain'])) {
            throw new \Exception("本地存储缺少配置参数");
        }
        return true;
    }

    /**
     * 检查保存路径
     * @return boolean
     * @throws \Exception
     */
    protected function checkPath() {
        $savePath = $this->getConfig('save_path');
        $absoluteDir = trim($savePath, '/') . '/' . trim($this->fileObject->saveDir, '/') . '/';
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
     * @return FileResult
     */
    public function save(): FileResult {
        $fr = FileResult::create();
        if (!$this->checkPath()) {
            return $fr->setErrorMsg('上传目录创建失败');
        }
        if ($this->fileObject->size > $this->fileObject->maxSize) {
            return $fr->setErrorMsg('上传文件过大');
        }
        $absolutePath = $this->getAbsolutePath($this->fileObject->filePath);
        if (!$this->fileObject->isCover && is_file($absolutePath)) {
            return $fr->setErrorMsg('目标文件已经存在');
        }
        //保存文件
        if (!empty($this->fileObject->fileData)) {
            $handle = fopen($absolutePath, "w+");
            fwrite($handle, $this->fileObject->fileData);
            fclose($handle);
            return $fr->setSuccessMsg();
        } else if (!empty($this->fileObject->fileTmpPath)) {
            if (move_uploaded_file($this->fileObject->fileTmpPath, $absolutePath)) {
                return $fr->setSuccessMsg();
            } else if (copy($this->fileObject->fileTmpPath, $absolutePath)) {
                //@unlink($this->fileObject->fileTmpPath);
                return $fr->setSuccessMsg();
            } else {
                $fold = fopen($this->fileObject->fileTmpPath, 'r');
                $fnew = fopen($absolutePath, 'w+');
                stream_copy_to_stream($fold, $fnew);
                fclose($fold);
                fclose($fnew);
                //@unlink($this->fileObject->fileTmpPath);
                return $fr->setSuccessMsg();
            }
        } else if (!empty($this->fileObject->fileBase64)) {
            $fileContent = base64_decode($this->fileObject->fileBase64);
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
     * @return FileResult
     */
    public function del($filePath = ''): FileResult {
        $fr = FileResult::create();
        if (empty($filePath) && !empty($this->fileObject)) {
            $filePath = $this->fileObject->filePath;
        }
        $absolutePath = $this->getAbsolutePath($filePath);
        if (!is_file($absolutePath)) {
            return $fr->setSuccessMsg('文件不存在');
        }
        if (@unlink($absolutePath)) {
            return $fr->setSuccessMsg('删除成功');
        }
        return $fr->setErrorMsg('删除失败');
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
        $absolutePath = $this->getAbsolutePath($filePath);
        if (is_file($absolutePath)) {
            return $fr->setSuccessMsg('文件存在');
        }
        return $fr->setErrorMsg('文件不存在');
    }

}
