<?php

namespace common\components;

use Yii;
use yii\filters\RateLimiter;
use yii\web\Request;
use yii\web\Response;
use yii\web\TooManyRequestsHttpException;
use api\controllers\RestControllerBase;

class MyRateLimiter extends RateLimiter
{
    private $endpoint;

    public function beforeAction($action)
    {
       //RestControllerBase
        $endpoint = Yii::$app->controller->id . "/" . Yii::$app->controller->action->id;
        Yii::$app->controller->endpoint = $endpoint;
        //echo $endpoint;

		return parent::beforeAction($action);
    }

}
