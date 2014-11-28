<?php
namespace YiiFileUploader;

use CException;
use CWebModule;
use Infotech\FileStorage\Storage\StorageInterface;
use Yii;

class Module extends CWebModule
{
    const TEMPORARY_STORAGE_PARAM = 'storage';

    public $defaultController = 'upload';

    public $controllerNamespace = '\YiiFileUploader\Controller';

    public $viewRoute = 'uploader/upload/get';

    public $defaultImage = null;

    /**
     * Path to jQuery-Ajax-Upload plugin
     * Default is getting by alias (vendor.codler.jQuery-Ajax-Upload)
     * @var null
     */
    public $defaultPluginPath = null;

    public function init()
    {
        $this->registerAssets();

        return parent::init();
    }

    public function getControllerPath()
    {
        return __DIR__ . '/Controller';
    }

    public function getTemporaryStorage()
    {
        $component = $this->getComponent(self::TEMPORARY_STORAGE_PARAM);

        if (!$component instanceof StorageInterface) {
            throw new CException(
                sprintf(
                    'Компонент "%s" не настроен в конфигурации модуля "%s" или не является хранилищем файлов',
                    self::TEMPORARY_STORAGE_PARAM,
                    $this->getName()
                )
            );
        }

        return $component;
    }

    protected function registerAssets()
    {
        /** @var \CClientScript $clientScript */
        $clientScript = Yii::app()->getClientScript();
        $path = $this->defaultPluginPath ?: Yii::getPathOfAlias('vendor.codler.jQuery-Ajax-Upload');
        $url = Yii::app()->assetManager->publish($path, false, -1);
        $clientScript->registerScriptFile($url.'/jquery.ajaxupload.js');
    }

    public function getUrl($path)
    {
        return $path
            ? Yii::app()->createUrl($this->viewRoute, ['filename' => $path])
            : '';
    }
}

