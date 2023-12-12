<?php

namespace frontend\assets;

use yii\web\AssetBundle;
use yii\web\View;

/**
 * Main frontend application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        //'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css',
        'css/fa/css/font-awesome.min.css',
        'css/flags/flag-icon.min.css',
        'css/site.css',
        'css/common.css',
        'css/main.css',
        'css/owl.carousel.min.css',        
        'https://fonts.googleapis.com/css?family=Open+Sans|Roboto',
        
    ];
    public $js = [
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
        // 'backend\assets\LeafletMapAsset'
    ];

    public function init()
    {
        parent::init();
        $this->js = [
            //'https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js',
            'https://cdnjs.cloudflare.com/ajax/libs/gsap/1.19.1/TweenMax.min.js',
            //'//platform-api.sharethis.com/js/sharethis.js#property=58e250c783b6f0001198da09&product=inline-share-buttons',
            //'js/vendor/jquery-scrolltofixed-min.js',
            //'js/vendor/readmore.min.js',     
            //'js/vendor/owl.carousel.min.js',
            //'https://www.gstatic.com/charts/loader.js',
            ];

        $this->jsOptions = [
            'position' => View::POS_BEGIN,
            //'async' => 'async',
            //'defer' => 'defer',
        ];

    }

}
