<?php
namespace api\controllers;

use Yii;
use common\components\Utility;
use common\components\MyCustomActiveRecord;
use common\components\MyRateLimiter;
use api\behaviours\ApiAuth;
use api\behaviours\VerbCheck;
use api\components\CustomHttpException;

use yii\db\Expression;
use yii\filters\AccessControl;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\RateLimiter;
use yii\web\Controller;
use yii\web\UnauthorizedHttpException;
use yii\web\BadRequestHttpException;



class UtilsController extends RestControllerBase
{
    //TODO:: security issues for getting tokens after log in.

    //public $layout = '@app/views/layouts/main';
    public $layout = false;

    public function behaviors() {
        $behaviors = parent::behaviors();
        return $behaviors + [
            'verbs' => [
                'class' => \yii\filters\VerbFilter::className(),
                'actions' => [
                    'index' => ['GET'],
                ],
            ],
            
        ];
       
    }

    public function actionIndex() {
        $o = (object) array("app"=>Yii::$app->name, "version"=>Yii::$app->params["apiVersion"], "endpoint"=>"utils");
        Yii::$app->api->sendSuccessResponse($o);
    }
    
    public function actionGeoIp() {
        $ip = Utility::getClientIp();
        $o = Utility::getGeoIp($ip);

        if ($o) {
            Yii::$app->api->sendSuccessResponse($o);
        } else {
            $e = json_encode(['message'=>"IP address not found."]);
            throw new CustomHttpException($e, CustomHttpException::UNPROCESSABLE_ENTITY);  
        }
       
    }



}
