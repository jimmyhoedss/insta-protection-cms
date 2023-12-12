<?php
/**
 * Created by PhpStorm.
 * User: zein
 * Date: 7/3/14
 * Time: 3:14 PM
 */

namespace backend\assets;

use yii\web\AssetBundle;
use yii\web\YiiAsset;

class LottieAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/style.css'
    ];
    public $js = [
        'https://cdnjs.cloudflare.com/ajax/libs/bodymovin/5.5.2/lottie.min.js'
    ];

    public $depends = [

    ];
}
