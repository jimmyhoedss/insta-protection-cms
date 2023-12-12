<?php

namespace backend\assets;

use yii;
use yii\web\AssetBundle;
use yii\web\View;
use common\assets\FontAwesome;
use yii\bootstrap\BootstrapPluginAsset;
use yii\jui\JuiAsset;
use yii\web\JqueryAsset;

//ref : https://www.chartjs.org/docs/latest/
/**
 * google chart asset bundle.
 */
class TestChart extends AssetBundle
{
    //public $basePath = '@webroot';
    //public $baseUrl = '@web';

    public $sourcePath = '@almasaeed2010/adminlte/bower_components/';

    public $css = [
        // 'https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css'
    ];
    public $js = [
        'jquery/dist/jquery.min.js',
        // 'bootstrap/dist/js/bootstrap.min.js',
        'chart.js/Chart.js',
        // 'fastclick/lib/fastclick.js'
    ];
    public $depends = [
        // 'yii\web\YiiAsset',
        //'yii\bootstrap\BootstrapAsset',
    ];

    public function init()
    {
        parent::init();
        // $this->js = [
        //     // 'https://dabeng.github.io/OrgChart/js/jquery.min.js',
        //        // 'https://dabeng.github.io/OrgChart/js/jquery.orgchart.js',
        //     'https://fperucic.github.io/treant-js/vendor/raphael.js',
        //     'https://fperucic.github.io/treant-js/Treant.js',
        //     // 'https://fperucic.github.io/treant-js/examples/tennis-draw/example7.js',
        //     // 'https://www.gstatic.com/charts/loader.js', //load org chart google
        //        // 'js/chart-org.js'
        //     ];

        $this->jsOptions = [
            'position' => View::POS_BEGIN,
            //'async' => 'async',
            //'defer' => 'defer',
        ];
    }
}
