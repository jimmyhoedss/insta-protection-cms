<?php
namespace common\components;

use Yii;
use \yii\db\Expression;
use yii\base\Model;
use yii\helpers\ArrayHelper;


class MyCustomModel extends Model {

    public $_errorMsgKeys;

    public function init() {
        $this->_errorMsgKeys = [];
    }

    public function addError($attribute, $error = '', $key = "") {
        parent::addError($attribute, $error);
        $this->_errorMsgKeys[$attribute] = $key;
    }
    

    public function clearErrors($attribute = null)
    {
        parent::clearErrors($attribute);
        if ($attribute === null) {
            $this->_errorMsgKeys = [];
        } else {
            unset($this->_errorMsgKeys[$attribute]);
        }
    }

    public function getErrorkey($attribute) {
        if (isset($this->_errorMsgKeys[$attribute])) {
            return $this->_errorMsgKeys[$attribute];
        } else {
            return "";
        }        

    }



}