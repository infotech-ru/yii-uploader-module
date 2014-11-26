<?php
/**
 * @author Мурат Эркенов <murat@11bits.net>
 */

namespace YiiFileUploader\Controller;

use Infotech\FileStorage\File\FileDescriptor;
use Infotech\FileStorage\Storage\StorageInterface;
use CController;
use CException;
use CUploadedFile;
use CWebApplication;
use WebUser;
use Yii;

class UploadController extends CController
{
    const FILE_PARAM_NAME = 'uploads';

    const ERROR_STORAGE_FAILURE   = 'ERROR_STORAGE_FAILURE';
    const ERROR_REQUEST_MALFORMED = 'ERROR_REQUEST_MALFORMED';

    public function actionIndex()
    {
        $fileIdentifier = null;
        $error = null;
        $list = [];

        if ($files = CUploadedFile::getInstancesByName(self::FILE_PARAM_NAME)) {
            try {
                foreach ($files as $file) {
                    $descriptor = new FileDescriptor($file->getTempName(), $file->getSize(), $file->getType());
                    $path = $this->createFilePath($file->getName());
                    $this->getTemporaryStorage()->put($path, $descriptor);
                    $list[] = $path;
                }

            } catch (CException $e) {
                $error = self::ERROR_STORAGE_FAILURE;
            }
        } else {
            $error = self::ERROR_REQUEST_MALFORMED;
        }

        echo json_encode([
            'data' => $list,
            'error' => $error
        ]);
    }

    public function actionGet($filename)
    {
        $file = $this->getTemporaryStorage()->get($filename);

        header('Content-Type: image/*');
        header('Last-Modified: Wed, 15 Sep 2004 12:00:00 GMT');
        header('Content-Length: ' . $file->getSize());
        
        echo file_get_contents($file->getPath());
    }

    public function actionDelete($filename)
    {
        $this->getTemporaryStorage()->delete($filename);

        echo json_encode(['success' => true]);
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
