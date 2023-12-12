<?php
/**
 * Author: Eugine Terentev <eugine@terentev.net>
 */

namespace common\widgets\MyUpload;

use yii\web\AssetBundle;

class MyUploadAsset extends AssetBundle
{

    public $depends = [
        \yii\web\JqueryAsset::class,
        \trntv\filekit\widget\BlueimpFileuploadAsset::class,
        // \trntv\filekit\widget\UploadAsset::class,
        // \rmrevin\yii\fontawesome\NpmFreeAssetBundle::class
    ];

    public $sourcePath = __DIR__ . '/assets';

    public $css = [
        YII_DEBUG ? 'css/upload-kit.css' : 'css/upload-kit.min.css'
    ];

    public $js = [
        YII_DEBUG ? 'js/MyUploadKit.js' : 'js/MyUploadKit.js'
    ];
}
