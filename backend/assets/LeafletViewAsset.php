<?php
namespace backend\assets;

use yii;
use yii\web\AssetBundle;
use yii\web\View;

/**
 * Main backend application asset bundle.
 */
class LeafletViewAsset extends AssetBundle
{
    //public $basePath = '@webroot';
    //public $baseUrl = '@web';
    public $sourcePath = "@backend/web"; 
    public $basePath ="@backend";
    
    public $css = [
        //'css/map.css',
    ];
    public $js = [
    ];
    public $depends = [
        //'yii\web\YiiAsset',
        'backend\assets\LeafletMapAsset',
    ];

    public function init()
    {
        parent::init();
        $this->js = [
            'js/leaflet-view-location.js',
            ];

        /*
        $this->jsOptions = [
            'position' => View::POS_BEGIN,
            //'async' => 'async',
            //'defer' => 'defer',
        ];
        */

    }
}
