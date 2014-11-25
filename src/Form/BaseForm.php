<?php
/**
 *
 * Author: terrasoff
 * Email: terrasoff@terrasoff.ru
 * Skype: tarasov.konstantin
 */

namespace YiiFileUploader\Form;

use CActiveRecord;
use CFormModel;
use ActiveRecord;

/**
 * TODO: Антон: как-то не логично это в модуле аплоадера держать.
 * может вынести это в неймспейс общих утилит
 * Infotech\Autocrm\Common\Forms\DomainMappedForm?
 *
 * Class BaseForm
 * @package Infotech\Autocrm\Modules\FileUploader\Form
 */
class BaseForm extends CFormModel
{
    /** @var CActiveRecord */
    private $domainObject;

    public function formToDomainAttrMap()
    {
        return [];
    }

    public function __construct(CActiveRecord $domainObject, $params = '')
    {
        $this->domainObject = $domainObject;

        // для обратной совместимости параметров
        if (is_string($params)) {
            $scenario = $params;
        }
        else {
            $scenario = isset($params['scenario']) ? $params['scenario'] : '';
        }

        parent::__construct($scenario);
    }

    public function attributeNames()
    {
        return array_keys($this->formToDomainAttrMap());
    }

    public function __get($name)
    {
        $attrMap = $this->formToDomainAttrMap();
        return isset($attrMap[$name]) ? $this->domainObject->{$attrMap[$name]} : parent::__get($name);
    }

    public function __set($name, $value)
    {
        $attrMap = $this->formToDomainAttrMap();
        if (isset($attrMap[$name])) {
            // TODO: подумать, как корректно обработать исключительные ситуации сеттера объекта домена
            $this->domainObject->{$attrMap[$name]} = $value;
        } else {
            parent::__set($name, $value);
        }
    }

    public function validate($attributes = null, $clearErrors = true)
    {
        parent::validate($attributes, $clearErrors);
        $this->domainObject->validate();
        $flippedAttrMap = array_flip($this->formToDomainAttrMap());
        foreach ($this->domainObject->getErrors() as $attrName => $errors) {
            if (isset($flippedAttrMap[$attrName])) {
                foreach ($errors as $error) {
                    $this->addError($flippedAttrMap[$attrName], $error);
                }
            }
        }
        return !$this->getErrors();
    }

    /**
     * @return CActiveRecord
     */
    public function getDomainObject()
    {
        return $this->domainObject;
    }

    /**
     * @param CActiveRecord $domainObject
     */
    public function setDomainObject(CActiveRecord $domainObject)
    {
        $this->domainObject = $domainObject;
    }
}