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

class HeartbeatAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [ ];
    public $js = [
        'https://cdnjs.cloudflare.com/ajax/libs/socket.io/2.0.3/socket.io.js',
        'https://cdnjs.cloudflare.com/ajax/libs/jquery-timeago/1.6.6/jquery.timeago.min.js',
        'js/widget/heartbeat.js'
    ];
    public $depends = [
        YiiAsset::class,
    ];
}
