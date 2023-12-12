<?php
namespace backend\assets;

use yii;
use yii\web\AssetBundle;
use yii\web\View;

/**
 * Main backend application asset bundle.
 */
class LeafletMapAsset extends AssetBundle
{
    //public $basePath = '@webroot';
    //public $baseUrl = '@web';
    public $sourcePath = "@backend/web"; 
    public $basePath ="@backend";
    
    public $css = [
        //'http://cdn.leafletjs.com/leaflet/v1.0.2/leaflet.css',
        'js/vendor/leaflet/leaflet.css',
        'https://api.tiles.mapbox.com/mapbox-gl-js/v0.34.0/mapbox-gl.css',
        'https://unpkg.com/leaflet.markercluster@1.0.4/dist/MarkerCluster.Default.css',
        'https://unpkg.com/leaflet.markercluster@1.0.4/dist/MarkerCluster.css',
        'https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/leaflet.fullscreen.css',
        'https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/leaflet.fullscreen.css',
        //'css/map.css',
    ];
    public $js = [
    ];
    public $depends = [
        //'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];

    public function init()
    {
        parent::init();
        $this->js = [
            'js/variables.js',
            'js/vendor/leaflet/leaflet.min.js',
            'js/leaflet-color-markers.js',            
            //'https://cdn.leafletjs.com/leaflet/v1.0.2/leaflet.js',
            'https://api.tiles.mapbox.com/mapbox-gl-js/v0.34.0/mapbox-gl.js',
            'https://unpkg.com/leaflet.markercluster@1.0.4/dist/leaflet.markercluster-src.js',
            'js/vendor/leaflet/leaflet-mapbox-gl.js',
            'https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/Leaflet.fullscreen.min.js',
            '//api.tiles.mapbox.com/mapbox.js/plugins/leaflet-omnivore/v0.3.1/leaflet-omnivore.min.js',
            //'js/vendor/leaflet-heat.js',
            'js/vendor/leaflet-ajax.min.js',
            'https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/Leaflet.fullscreen.min.js',
            ];

        $this->jsOptions = [
            'position' => View::POS_BEGIN,
            //'async' => 'async',
            //'defer' => 'defer',
        ];

    }
}
