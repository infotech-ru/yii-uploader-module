<?php
/**
 * @author Мурат Эркенов <murat@11bits.net>
 */

namespace YiiFileUploader\Form;

use Infotech\FileStorage\Storage\StorageInterface;
use CBehavior;
use CException;
use CFormModel;
use CValidator;
use Yii;

class FileFormBehavior extends CBehavior
{
    private $oldValues = [];

    /**
     * @var StorageInterface
     */
    private $temporaryStorage;

    /**
     * @var StorageInterface
     */
    private $permanentStorage;

    /**
     * @var string
     */
    private $permanentStoragePrefix;

    /**
     * @param StorageInterface $permanentStorage
     * @param StorageInterface $temporaryStorage
     * @param string $permanentStoragePrefix
     */
    public function __construct(
        StorageInterface $permanentStorage,
        StorageInterface $temporaryStorage,
        $permanentStoragePrefix = '')
    {
        $this->permanentStorage = $permanentStorage;
        $this->temporaryStorage = $temporaryStorage;
        $this->permanentStoragePrefix = $permanentStoragePrefix;
    }

    public function attach($owner)
    {
        if (!$owner instanceof CFormModel) {
            throw new CException('Это поведение может быть использовано только для моделей форм');
        }

        parent::attach($owner);

        /** @var $validator CValidator */
        foreach ($owner->getValidators() as $validator) {
            if ($validator instanceof FileValidator) {
                foreach ($validator->attributes as $fieldName) {
                    $this->oldValues[$fieldName] = $this->getOwner()->$fieldName;
                }
            }
        }
    }

    public function getFile($attrName)
    {
        if ($path = $this->getOwner()->$attrName) {
            if ($this->temporaryStorage->exists($path)) {
                return $this->temporaryStorage->get($path);
            } elseif ($this->permanentStorage->exists($path)) {
                return $this->permanentStorage->get($path);
            }
        }

        return null;
    }

    public function persistFiles($sanitize = false)
    {
        $owner = $this->getOwner();

        foreach ($this->oldValues as $fieldName => $oldValue) {
            $newValue = $owner->$fieldName;

            if ($newValue != $oldValue) {
                if ($oldValue) {
                    $this->removeFile($oldValue);
                }
                if ($newValue) {
                    $owner->$fieldName = $this->persistFile($newValue, $sanitize);
                }
            }
        }
    }

    public function removeFile($value)
    {
        $this->permanentStorage->delete($value);
    }

    private function persistFile($value, $sanitize = false)
    {
        $permanentPath = $this->generatePermanentStorageFilePath($value, $sanitize);

        if ($tmpFile = $this->temporaryStorage->get($value)) {
            $this->permanentStorage->put($permanentPath, $tmpFile);
            $this->temporaryStorage->delete($value);
        }

        return $permanentPath;
    }

    /**
     * @param $temporaryPath
     * @return string
     */
    private function generatePermanentStorageFilePath($temporaryPath, $sanitize = false)
    {
        if ($sanitize) {
            $data = pathinfo($temporaryPath);
            $data['filename'] = md5($data['filename']);

            // TODO: check
            // preg_replace('/[^A-Za-zА-Яа-я0-9_]+/Si', '_', $data['filename']);

            $temporaryPath = $data['dirname'] . DIRECTORY_SEPARATOR . $data['filename'] . '.' . $data['extension'];
        }

        return $this->permanentStoragePrefix . $temporaryPath;
    }
}
