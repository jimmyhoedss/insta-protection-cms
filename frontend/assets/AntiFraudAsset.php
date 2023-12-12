<?php

namespace frontend\assets;

use yii\web\AssetBundle;
use yii\web\View;

/**
 * Main frontend application asset bundle.
 */
class AntiFraudAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        //'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css',
        //'css/anti-fraud.css',
    ];
    public $js = [
        'js/anti-fraud.js',
        'js/jsQR.js',
        'https://unpkg.com/axios/dist/axios.min.js',
    ];
    public $depends = [
    ];

}
