<?php
namespace backend\assets;

use yii;
use yii\web\AssetBundle;
use yii\web\View;

/**
 * Main backend application asset bundle.
 */
class OpenlayersMapAsset extends AssetBundle
{
    //public $basePath = '@webroot';
    //public $baseUrl = '@web';
    public $sourcePath = "@backend/web"; 
    public $basePath ="@backend";
    
    public $css = [
        //'http://cdn.leafletjs.com/leaflet/v1.0.2/leaflet.css',
        'js/openlayers/style.css',
        'https://cdn.rawgit.com/openlayers/openlayers.github.io/master/en/v5.1.3/css/ol.css',
        'https://cdn.rawgit.com/Viglino/ol-ext/master/dist/ol-ext.min.css',
        //'https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0-rc.2/css/materialize.min.css',
        'https://fonts.googleapis.com/icon?family=Material+Icons',
        

        //'css/map.css',
    ];
    public $js = [
    ];
    public $depends = [
        'yii\web\YiiAsset',
        //'yii\bootstrap\BootstrapAsset',
    ];

    public function init()
    {
        parent::init();
        $this->js = [
            'https://cdn.rawgit.com/openlayers/openlayers.github.io/master/en/v5.1.3/build/ol.js',
            'https://cdn.rawgit.com/Viglino/ol-ext/master/dist/ol-ext.min.js',
            //'js/openlayers/app.js',
        ];

        $this->jsOptions = [
            'position' => View::POS_HEAD,
            //'async' => 'async',
            //'defer' => 'defer',
        ];

    }
}
