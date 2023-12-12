<?php
namespace backend\assets;

use yii;
use yii\web\AssetBundle;
use yii\web\View;

/**
 * Main backend application asset bundle.
 */
class LeafletDrawAsset extends AssetBundle
{
    //public $basePath = '@webroot';
    //public $baseUrl = '@web';
    public $sourcePath = "@backend/web"; 
    public $basePath ="@backend";
    
    public $css = [
        'js/vendor/leaflet-draw/Leaflet.draw.css',
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
            'js/vendor/leaflet-draw/Leaflet.draw.js',
            'js/vendor/leaflet-draw/Leaflet.Draw.Event.js',
            'js/vendor/leaflet-draw/Toolbar.js',
            'js/vendor/leaflet-draw/Tooltip.js',
            'js/vendor/leaflet-draw/ext/GeometryUtil.js',
            'js/vendor/leaflet-draw/ext/LatLngUtil.js',
            'js/vendor/leaflet-draw/ext/LineUtil.Intersect.js',
            'js/vendor/leaflet-draw/ext/Polygon.Intersect.js',
            'js/vendor/leaflet-draw/ext/Polyline.Intersect.js',
            'js/vendor/leaflet-draw/ext/TouchEvents.js',
            'js/vendor/leaflet-draw/draw/DrawToolbar.js',
            'js/vendor/leaflet-draw/draw/handler/Draw.Feature.js',
            'js/vendor/leaflet-draw/draw/handler/Draw.SimpleShape.js',
            'js/vendor/leaflet-draw/draw/handler/Draw.Polyline.js',
            'js/vendor/leaflet-draw/draw/handler/Draw.Circle.js',
            'js/vendor/leaflet-draw/draw/handler/Draw.Marker.js',
            'js/vendor/leaflet-draw/draw/handler/Draw.Polygon.js',
            'js/vendor/leaflet-draw/draw/handler/Draw.Rectangle.js',
            'js/vendor/leaflet-draw/edit/EditToolbar.js',
            'js/vendor/leaflet-draw/edit/handler/EditToolbar.Edit.js',
            'js/vendor/leaflet-draw/edit/handler/EditToolbar.Delete.js',
            'js/vendor/leaflet-draw/Control.Draw.js',
            'js/vendor/leaflet-draw/edit/handler/Edit.Poly.js',
            'js/vendor/leaflet-draw/edit/handler/Edit.SimpleShape.js',
            'js/vendor/leaflet-draw/edit/handler/Edit.Circle.js',
            'js/vendor/leaflet-draw/edit/handler/Edit.Rectangle.js',
            'js/vendor/leaflet-draw/edit/handler/Edit.Marker.js',
            


            

            ];

        $this->jsOptions = [
            'position' => View::POS_BEGIN,
            //'async' => 'async',
            //'defer' => 'defer',
        ];

    }
}
