<?php
/**
 * Created by PhpStorm.
 * User: zein
 * Date: 7/3/14
 * Time: 3:14 PM
 */

namespace dashboard\assets;

use common\assets\AdminLte;
use yii\web\AssetBundle;
use yii\web\YiiAsset;

class DashboardAsset extends AssetBundle
{
    /**
     * @var string
     */
    public $basePath = '@webroot';
    /**
     * @var string
     */
    public $baseUrl = '@web';

    /**
     * @var array
     */
    public $css = [
        'css/style.css',
        'css/site.css'
    ];
    /**
     * @var array
     */
    public $js = [
        'js/app.js',
        '//kit.fontawesome.com/8c35976500.js'
    ];

    /**
     * @var array
     */
    public $depends = [
        YiiAsset::class,
        AdminLte::class,
    ];
}
