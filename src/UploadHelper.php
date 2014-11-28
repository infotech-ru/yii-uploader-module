<?php
namespace YiiFileUploader;

use CActiveRecord;
use CClientScript;
use CHtml;
use CMap;
use Yii;

class UploadHelper extends CHtml
{
    public static function activeImageUploader(
        CActiveRecord $model,
        $attribute = 'files',
        array $options = [],
        array $htmlOptions = [])
    {
        $options['button'] = false;

        return
            self::activeImage($model, $attribute, $options, $htmlOptions) .
            self::activeUploadField($model, $attribute, $options, $htmlOptions)
        ;

    }
    public static function activeImage(
        CActiveRecord $model,
        $attribute = 'files',
        array $options = [],
        array $htmlOptions = [])
    {
        $options = CMap::mergeArray([
            'module' => 'uploader',
            'title' => 'Изображение',
        ], $options);

        /** @var Module $module */
        $module = Yii::app()->getModule($options['module']);
        $images = explode(',', $model->{$attribute});
        $url = isset($images[0])
            ? $module->getUrl($images[0])
            : $module->defaultImage;
        ;

        return CHtml::image($url, $options['title'], $htmlOptions);
    }

    public static function activeUploadField(
        CActiveRecord $model,
        $attribute = 'files',
        array $options = [],
        array $htmlOptions = [])
    {
        $options = CMap::mergeArray([
            'button' => true,
            'id' => 'uploader',
        ], $options);

        $name = CHtml::resolveName($model, $attribute);

        // после загрузки файлов
        // заполняем поле с файлами для данной модели
        // значениями загруженных файлов
        $script = <<< SCRIPT
$(function() {
    app.on('uploader:uploaded', function(data) {
        if (data.id == undefined || data.id === "{$options['id']}") {
            $('[name="{$name}"]').val(data.files.join(','))
        }
    })
});
SCRIPT;

        $button = $options['button']
            ? CHtml::button('Загрузить', $htmlOptions)
            : '';

        return
            CHtml::script($script) .
            CHtml::activeHiddenField($model, $attribute) .
            $button
        ;
    }
}

