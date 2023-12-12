<?php

namespace backend\assets;

use yii;
use yii\web\AssetBundle;
use yii\web\View;

/**
 * Firebase / Firestore asset bundle.
 */
class FirebaseAsset extends AssetBundle
{
    //public $basePath = '@webroot';
    //public $baseUrl = '@web';
    public $sourcePath = "@backend/web"; 
    public $basePath ="@backend";
    
    public $css = [
        //'css/site.css',
    ];
    public $js = [
    ];
    public $depends = [
        //'yii\web\YiiAsset',
        //'yii\bootstrap\BootstrapAsset',
    ];

    public function init()
    {
        parent::init();
        $this->js = [
            'https://www.gstatic.com/firebasejs/4.9.1/firebase.js',
            'https://www.gstatic.com/firebasejs/4.9.1/firebase-firestore.js'
            ];

        $this->jsOptions = [
            'position' => View::POS_BEGIN,
            //'async' => 'async',
            //'defer' => 'defer',
        ];
    }
}
