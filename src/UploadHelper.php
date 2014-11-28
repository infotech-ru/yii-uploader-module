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
            CHtml::button('Удалить', ['class' => 'delete']) .
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
            'default-image' => null,
        ], $options);


        /** @var Module $module */
        $images = explode(',', $model->{$attribute});
        $module = Yii::app()->getModule($options['module']);
        if (empty($images[0])) {
            $defaultImage = $options['default-image'] !== null
                ? $options['default-image']
                : $module->defaultImage;
            $url = $defaultImage;
        } else {
            $url = $module->getUrl($images[0]);
        }

        $htmlOptions['data-default-image'] = $defaultImage;

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
        ], $options);

        $name = CHtml::resolveName($model, $attribute);

        $button = $options['button']
            ? CHtml::button('Загрузить', $htmlOptions)
            : '';

        return
            CHtml::activeHiddenField($model, $attribute) .
            $button
        ;
    }
}

