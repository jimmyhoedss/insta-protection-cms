<?php
namespace api\controllers;

use Yii;
use yii\db\Expression;
use yii\filters\AccessControl;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\RateLimiter;
use yii\web\Controller;
use yii\web\UnauthorizedHttpException;
use yii\web\BadRequestHttpException;

use api\behaviours\ApiAuth;
use api\behaviours\VerbCheck;
use api\components\CustomHttpException;

use common\components\Utility;
use common\components\MyCustomActiveRecord;

use common\models\UserCase;
use common\models\User;
use common\models\UserPlanDetail;
use common\models\QcdRepairCentre;
use common\models\QcdRetailStore;
use common\models\QcdDeviceMaker;
//use common\models\QcdDeviceMakerRepairCentre;
//use common\models\QcdDeviceMakerRetailStore;
use common\models\InstapPlanPool;


class QcdController extends RestControllerBase
{
    //TODO:: security issues for getting tokens after log in.

    //public $layout = '@app/views/layouts/main';
    public $layout = false;

    public function behaviors() {
        return array_merge(parent::behaviors(), [
            
            'verbs' => [
                'class' => \yii\filters\VerbFilter::className(),
                'actions' => [
                    'index' => ['GET'],
                    'list-repair-centre' => ['GET'],
                    'list-retail-store' => ['GET'],
                ],
            ],
            'authenticator' => [
                'class' => HttpBearerAuth::className(),
                'except' => ['index','list-repair-centre', 'list-retail-store'],
            ],
            'ActiveTimestampBehavior' => [
                'class' => \common\behaviors\ActiveTimestampBehavior::className(),
                'attribute' => 'active_at'
            ],
        ]);       
       
    }

    public function actionIndex() {
        $o = (object) array("app"=>Yii::$app->name, "version"=>Yii::$app->params["apiVersion"], "endpoint"=>"claim");
        Yii::$app->api->sendSuccessResponse($o);
    }


    public function actionListRepairCentre($plan_pool_id) {
        //should pass plan pool id to get brand?

        // $device_maker = QcdDeviceMaker::find()->where(['LOWER(device_maker)' => $b])->one();
        $planPool = InstapPlanPool::find()->where(['id' => $plan_pool_id])->one();
        if(!$planPool) {
            $str =  Utility::jsonifyError("plan_pool_id", Yii::t('common', "Plan pool id not found."));
            throw new CustomHttpException($str, CustomHttpException::UNPROCESSABLE_ENTITY);
        }
        
        $brand = $planPool->userPlan->details->sp_brand;
        $region_id = $planPool->region_id;
        $b = strtolower($brand);
        
        $modelBrand = QcdDeviceMaker::find()->where(['LOWER(device_maker)' => $b])->one();

        if(empty($modelBrand)) {
            $str =  Utility::jsonifyError("brand", Yii::t('common', "Brand not available."));
            throw new CustomHttpException($str, CustomHttpException::UNPROCESSABLE_ENTITY);

        }
        $repair_centres = QcdRepairCentre::listRepairCentre($modelBrand, $region_id, $planPool->plan_id);        
        if(isset($repair_centres) != null) {
            
            Yii::$app->api->sendSuccessResponse($repair_centres);

        }else {
            $str =  Utility::jsonifyError("", Yii::t('common', "No Repair centre found."));
            throw new CustomHttpException($str, CustomHttpException::UNPROCESSABLE_ENTITY);
        }
        
    }

    public function actionListRetailStore($plan_pool_id) {
        //should pass plan pool id to get brand?

        // $device_maker = QcdDeviceMaker::find()->where(['LOWER(device_maker)' => $b])->one();
        $planPool = InstapPlanPool::find()->where(['id' => $plan_pool_id])->one();
        if(!$planPool) {
            $str =  Utility::jsonifyError("plan_pool_id", Yii::t('common', "Plan pool id not found."));
            throw new CustomHttpException($str, CustomHttpException::UNPROCESSABLE_ENTITY);
        }
        
        $brand = $planPool->userPlan->details->sp_brand;
        $region_id = $planPool->region_id;
        $b = strtolower($brand);
        
        $modelBrand = QcdDeviceMaker::find()->where(['LOWER(device_maker)' => $b])->one();

        if(empty($modelBrand)) {
            $str =  Utility::jsonifyError("brand", Yii::t('common', "Brand not available."));
            throw new CustomHttpException($str, CustomHttpException::UNPROCESSABLE_ENTITY);

        }
        $retail_stores = QcdRetailStore::listRetailStore($modelBrand, $region_id, $planPool->plan_id);        
        if(isset($retail_stores) != null) {
            
            Yii::$app->api->sendSuccessResponse($retail_stores);

        }else {
            $str =  Utility::jsonifyError("", Yii::t('common', "No Retail Store found."));
            throw new CustomHttpException($str, CustomHttpException::UNPROCESSABLE_ENTITY);
        }
        
    }
}
