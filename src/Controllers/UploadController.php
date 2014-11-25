<?php
/**
 * @author Мурат Эркенов <murat@11bits.net>
 */

namespace YiiFileUploader\Controllers;

use Infotech\FileStorage\File\FileDescriptor;
use Infotech\FileStorage\Storage\StorageInterface;
use CController;
use CException;
use CUploadedFile;
use CWebApplication;
use WebUser;
use Yii;
use YiiFileUploader\FileUploaderModule;

class UploadController extends CController
{
    const FILE_PARAM_NAME = 'files';

    const ERROR_STORAGE_FAILURE   = 'ERROR_STORAGE_FAILURE';
    const ERROR_REQUEST_MALFORMED = 'ERROR_REQUEST_MALFORMED';

    public function actionIndex()
    {
        $fileIdentifier = null;
        $error = null;

        if ($file = CUploadedFile::getInstanceByName(self::FILE_PARAM_NAME)) {
            try {
                $descriptor = new FileDescriptor($file->getTempName(), $file->getSize(), $file->getType());
                $path = $this->createFilePath($file->getName());
                $this->getTemporaryStorage()->put($path, $descriptor);
                $fileIdentifier = $path;
            } catch (CException $e) {
                $error = self::ERROR_STORAGE_FAILURE;
            }
        } else {
            $error = self::ERROR_REQUEST_MALFORMED;
        }

        echo json_encode([
            'result' => $fileIdentifier,
            'error' => $error
        ]);
    }

    /**
     * @param string $fileName
     * @return string
     */
    private function createFilePath($fileName)
    {
        return substr(md5(microtime()), 0, 4) . '/' . $fileName;
    }

    /**
     * @return StorageInterface
     */
    private function getTemporaryStorage()
    {
        return $this->getModule()->getTemporaryStorage();
    }

}
