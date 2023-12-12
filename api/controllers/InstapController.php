<?php

namespace api\controllers;

use Yii;

use yii\web\Controller;
use yii\web\UploadedFile;
use yii\web\HttpException;
use yii\web\UnauthorizedHttpException;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

use yii\filters\AccessControl;

use common\models\User;
use common\models\DealerCompany;
use common\models\DealerUser;
use common\models\DealerAdHocOrder;
use common\models\InstapPlan;
use common\models\InstapPlanPool;
use common\models\InstapPromotion;
use common\components\Utility;
use common\components\MyCustomActiveRecord;

use api\behaviours\ApiAuth;
use api\behaviours\VerbCheck;
use api\components\HttpBearerAuth;
use api\components\CustomHttpException;


class InstapController extends RestControllerBase
{
    public $layout = false;

    public function behaviors() {
        return array_merge(parent::behaviors(), [
            
            'verbs' => [
                'class' => \yii\filters\VerbFilter::className(),
                'actions' => [
                    'index' => ['GET'],
                    'promotion' => ['GET'],
                ],
            ],
            'authenticator' => [
                'class' => HttpBearerAuth::className(),
                'except' => ['index'],
            ],
            'ActiveTimestampBehavior' => [
                'class' => \common\behaviors\ActiveTimestampBehavior::className(),
                'attribute' => 'active_at'
            ],
            
        ]);
    }

    public function actionIndex()  {
        $o = (object) array("app"=>Yii::$app->name, "version"=>Yii::$app->params["apiVersion"], "endpoint"=>"instap");
        Yii::$app->api->sendSuccessResponse($o);
    }
    
    public function actionPromotion(){        
        $region_id = Yii::$app->user->identity->region_id;
        $ip = InstapPromotion::find()->andWhere(['region_id'=>$region_id])->andWhere(['status'=>MyCustomActiveRecord::STATUS_ENABLED])->all();
        
        $d = InstapPromotion::toObjectArray($ip);
        Yii::$app->api->sendSuccessResponse($d);
    }

}