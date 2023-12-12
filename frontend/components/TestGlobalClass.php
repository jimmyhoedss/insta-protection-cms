<?php
namespace frontend\components;

use Yii;

class TestGlobalClass extends \yii\base\Component{
    public function init() {
    	//Run this before other controllers.
    	//set backend timezone from key storage.
		//echo "Hi";
		//echo Yii::getVersion();

        //$cookies = Yii::$app->request->cookies;
        //$cookies['timezone']
        //print_r($cookies['_language']);
        /**/

        print_r(Yii::$app->language);

        parent::init();


    }
}