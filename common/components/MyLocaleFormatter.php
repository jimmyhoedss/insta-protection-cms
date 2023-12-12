<?php
namespace common\components;

use Yii;
use \kartik\datecontrol\Module;

class MyLocaleFormatter extends \yii\base\Component{
    public function init() {
        //Run this before other controllers.
        //set backend timezone from key storage.
        //echo "Hi";
        //echo Yii::getVersion();

        //$cookies = Yii::$app->request->cookies;
        //$cookies['timezone']
        //print_r($cookies['_language']);
        /**/

        parent::init();
    }

    public function setFormat($lang) {
        //Yii::$app->language
        
        if ($lang == "zh-CN") {
            Yii::$app->formatter->dateFormat = "php:Y年m月d日";
            $module = Yii::$app->getModule('datecontrol');
            $module->displaySettings["date"] = "php:Y年m月d日";
            //print_r($module->displaySettings["date"]);            
        }
        //print_r(Yii::$app->language);
        //echo "blah blah blah";
    }
}