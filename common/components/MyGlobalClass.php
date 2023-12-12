<?php
namespace common\components;

use Yii;

class MyGlobalClass extends \yii\base\Component{
    public function init() {
    	//Run this before other controllers.
    	//set backend timezone from key storage.
		//echo "Hi";
		//echo Yii::getVersion();

        /*
        $cookies = Yii::$app->request->cookies;
        
        if (isset($cookies['timezone'])) {
            Yii::$app->timeZone = Yii::$app->formatter->timeZone = $cookies['timezone']->value;
        }
        */

        parent::init();


    }
}