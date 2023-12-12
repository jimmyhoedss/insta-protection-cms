<?php

namespace backend\assets;

use yii;
use yii\web\AssetBundle;
use yii\web\View;

/**
 * google chart asset bundle.
 */
class ChartAsset extends AssetBundle
{
    //public $basePath = '@webroot';
    //public $baseUrl = '@web';

    public $sourcePath = "@backend/web"; 
    public $basePath ="@backend";
    public $css = [
        'css/org_chart.css',
        'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.css'
        // 'https://dabeng.github.io/OrgChart/css/jquery.orgchart.css'
        // 'https://fperucic.github.io/treant-js/Treant.css',
        // 'https://fperucic.github.io/treant-js/examples/tennis-draw/example7.css'
        // 'https://fperucic.github.io/treant-js/Treant.css',
        // 'https://fperucic.github.io/treant-js/examples/basic-example/basic-example.css',
    ];
    public $js = [
    ];
    public $depends = [
        // 'yii\web\YiiAsset',
        //'yii\bootstrap\BootstrapAsset',
    ];

    public function init()
    {
        parent::init();
        $this->js = [
            // 'https://dabeng.github.io/OrgChart/js/jquery.min.js',
               // 'https://dabeng.github.io/OrgChart/js/jquery.orgchart.js',
            'https://fperucic.github.io/treant-js/vendor/raphael.js',
            'https://fperucic.github.io/treant-js/Treant.js',
            'https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.20.1/moment.min.js', //note: time series chart need this lib
            'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js',
            // 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.min.js'
            // 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.bundle.min.js'
            // 'https://fperucic.github.io/treant-js/examples/tennis-draw/example7.js',
            // 'https://www.gstatic.com/charts/loader.js', //load org chart google
               // 'js/chart-org.js'
            ];

        $this->jsOptions = [
            'position' => View::POS_HEAD,
            //'async' => 'async',
            //'defer' => 'defer',
        ];
    }
}
