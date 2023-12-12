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

class AccessDeviceAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [ ];
    public $js = [
        //'https://cdnjs.cloudflare.com/ajax/libs/socket.io/2.0.3/socket.io.js',
        'js/widget/access-device.js'
    ];
    public $depends = [
        YiiAsset::class,
    ];
}
