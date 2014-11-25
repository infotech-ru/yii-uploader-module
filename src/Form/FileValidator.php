<?php
/**
 * @author Мурат Эркенов <murat@11bits.net>
 */

namespace YiiFileUploader\Form;

use CException;
use CModel;
use CValidator;
use Infotech\FileStorage\File\FileDescriptor;
use Yii;

class FileValidator extends CValidator
{

    /**
     * @var bool
     */
    public $allowEmpty = false;

    /**
     * @var int
     */
    public $maxSize;

    /**
     * @var array
     */
    public $types;


    /**
     * @param CModel|FileFormBehavior $model
     * @param string $attribute
     */
    protected function validateAttribute($model, $attribute)
    {
        $this->checkBehaveLike($model, 'Infotech\Autocrm\Modules\FileUploader\Form\FileFormBehavior');

        if (null !== $file = $model->getFile($attribute)) {
            foreach ($this->checkFileErrors($file, $attribute) as $error) {
                $this->addError($model, $attribute, $error);
            }
        } elseif (!$this->allowEmpty) {
            $this->addError($model, 'file', 'Файл не загружен');
        }
    }

    private function checkFileErrors(FileDescriptor $file)
    {
        $errors = [];

        if ($file->getSize() > $this->maxSize) {
            $errors[] = sprintf('Файл "{attribute}" слишком большой (должен быть не больше %s КиБ)', $this->maxSize);
        }

        if (is_array($this->types) && sizeof($this->types) && !in_array($file->getExtension(), $this->types)) {
            $errors[] = sprintf(
                'Файл "{attribute}" не соответствует ни одному из перечисленных типов: %s',
                implode(', ', $this->types)
            );
        }

        return $errors;
    }

    private function checkBehaveLike(CModel $model, $behaviourClassName)
    {
        foreach ($model->behaviors() as $behaviorName => $behaviorConfig) {
            if ($model->asa(is_int($behaviorName) ? $behaviorConfig : $behaviorName) instanceof $behaviourClassName) {
                return;
            }
        }

        throw new CException(
            'Валидатор ' . __CLASS__ . ' применим только к моделям с поведением ' . $behaviourClassName
        );
    }
}
