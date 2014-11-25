<?php
/**
 * @author Anton Tyutin <anton@tyutin.ru>
 */

namespace YiiFileUploader\Widgets;

use CException;
use CFormModel;
use CWebApplication;
use CWidget;
use Html;
use Yii;
use YiiFileUploader\Form\FileFormBehavior;
use YiiFileUploader\Form\FileValidator;
use YiiFileUploader\Controllers\UploadController;

class FileUploadWidget extends CWidget
{
    /**
     * Модель формы с поведением FileFormBehavior
     * @var CFormModel|FileFormBehavior
     */
    public $form;

    /**
     * Надпись на кнопке
     * @var string
     */
    public $label = 'Загрузить';

    /**
     * Имя поля формы с типом "файл"
     * @var string
     */
    public $attribute = 'files';

    /**
     * Настройки кнопки выбора
     * @var string
     */
    public $fileButtonOptions = [];

    public function run()
    {
        if (!$validator = $this->findFileValidator($this->form, $this->attribute)) {
            throw new CException(sprintf('Поле "%s" формы не является файлом', $this->attribute));
        }

        $this->registerAssets();

        $fileDescriptor = $this->form->getFile($this->attribute);

        $htmlOptions = [
            'name'         => Html::resolveName($this->form, $this->attribute),
            'value'        => $this->form[$this->attribute],
            'file_name'    => $fileDescriptor ? $fileDescriptor->getBasename() : '',
            'file_size'    => $fileDescriptor ? $fileDescriptor->getSize() : '',
            'ajax_param'   => UploadController::FILE_PARAM_NAME,
            'url'          => $this->getApp()->createUrl('uploader/upload/index'),
            'data-options' => json_encode([
                'fileButton' => $this->getFileButtonOptions(),
            ])
        ];

        // приоритет у form->types
        if (isset($this->form->filetypes) && count($this->form->filetypes) > 0)
            $htmlOptions['allowed_types'] = implode(',', $this->form->filetypes);
        else if (is_array($validator->types) && sizeof($validator->types))
            $htmlOptions['allowed_types'] = implode(',', $validator->types);

        if ($validator->maxSize > 0) {
            $htmlOptions['max_size'] = $validator->maxSize;
        }

        echo Html::tag('file', $htmlOptions);
    }

    /**
     * @param CFormModel $form
     * @param string $attrName
     * @return FileValidator|null
     */
    protected function findFileValidator(CFormModel $form, $attrName)
    {
        foreach ($form->getValidators($attrName) as $validator) {
            if ($validator instanceof FileValidator) {
                return $validator;
            }
        }

        return null;
    }

    protected function registerAssets()
    {
        /** @var \CClientScript $clientScript */
        $clientScript = Yii::app()->getClientScript();

        $path = __DIR__ . '/../assets/';
        $url = Yii::app()->assetManager->publish($path, false, -1, YII_DEBUG);

        $clientScript->registerScriptFile($url.'/query.iframe-transport.js');
        $clientScript->registerScriptFile($url.'/jquery.fileupload.js');
    }

    private function getFileButtonOptions()
    {
        return array_merge(
            [
                'label' => $this->label,
                'class' => 'choose_file btn btn-primary',
            ],
            $this->fileButtonOptions ?: []
        );
    }

} 
