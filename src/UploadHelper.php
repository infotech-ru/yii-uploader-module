<?php
namespace YiiFileUploader;

use CActiveRecord;
use CClientScript;
use CHtml;
use CMap;
use Yii;

class UploadHelper extends CHtml
{
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
        $image = isset($images[0])
            ? $images[0]
            : null;
        ;

        return CHtml::image($module->getUrl($image), $options['title']);
    }

    public static function activeUploadField(
        CActiveRecord $model,
        $attribute = 'files',
        array $options = [],
        array $htmlOptions = [])
    {
        $options = CMap::mergeArray([
            'id' => 'uploader',
        ], $options);

        $name = CHtml::resolveName($model, $attribute);

        $script = <<< SCRIPT
app.on('uploader:upload', function(data) {
    if (data.id == undefined || data.id === "{$options['id']}") {
        $('[name="{$name}"]').val(data.files.join(','))
    }
})
SCRIPT;
        /** @var CClientScript $cs */
        $cs = Yii::app()->clientScript;
        $cs->registerScript('uploader', $script, CClientScript::POS_READY);

        return
            CHtml::activeHiddenField($model, $attribute) .
            CHtml::button('Добавить', $htmlOptions)
        ;
    }
}

