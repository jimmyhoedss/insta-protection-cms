<?php
namespace backend\assets;

use yii\web\AssetBundle;

class DebugToolsAsset extends AssetBundle
{
    //public $basePath = '@webroot';
    //public $baseUrl = '@web';
    public $sourcePath = "@backend/web"; 
    public $basePath ="@backend";

    public $css = [
        //'css/common.css',
        //'css/main.css'
        //'css/style.css'
    ];
    public $js = [
        'js/debug-tools/app.js',
    ];

    public $depends = [
        'backend\assets\BackendAsset',
    ];
}
