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
            'default-image' => null,
        ], $options);

        /** @var Module $module */
        $module = Yii::app()->getModule($options['module']);

        $defaultImage = $options['default-image'] !== null
            ? $options['default-image']
            : $module->defaultImage;

        $images = explode(',', $model->{$attribute});
        if (empty($images[0])) {
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
            'addButton' => true,
            'deleteButton' => true,
        ], $options);

        $name = CHtml::resolveName($model, $attribute);

        $addButton = $options['addButton']
            ? CHtml::button('Загрузить', $htmlOptions)
            : '';

        $deleteButton = $options['deleteButton']
            ? CHtml::button('удалить', $htmlOptions)
            : '';

        return
            $addButton .
            CHtml::activeHiddenField($model, $attribute) .
            $deleteButton
        ;
    }
}

