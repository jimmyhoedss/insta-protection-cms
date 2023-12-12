<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class LoginAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = [
        'css/social/bootstrap-social.css',
    ];

    public $js = [
        //'js/app.js',
        //"jQuery('.oauth-holder').authchoice();"
    ];

    public $depends = [
        'yii\authclient\widgets\AuthChoiceAsset',
    ];
}
