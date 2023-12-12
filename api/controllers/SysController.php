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
use yii\web\UploadedFile;

use api\behaviours\ApiAuth;
use api\behaviours\VerbCheck;
use api\components\CustomHttpException;

use common\components\Utility;
use common\components\MyCustomActiveRecord;
use common\components\MyRateLimiter;

use common\jobs\TestQueueJob;
use common\jobs\EmailQueueJob;
use common\commands\SendEmailCommand;


use common\models\UserCase;
use common\models\User;
use common\models\InstapPlanPool;
use common\models\KeyStorageItem;
use common\models\SysSocketNotification;

use common\models\form\UploadForm;
use common\models\form\DeviceAssessmentForm;

use common\models\fcm\FcmPlanStatusChanged;
use common\models\fcm\FcmCaseStatusChanged;

class SysController extends RestControllerBase
{
    //TODO:: security issues for getting tokens after log in.

    //public $layout = '@app/views/layouts/main';
    public $layout = false;

    public function behaviors() {
        //loynote: for cors, need to re-add authenticator??
        //ref: https://stackoverflow.com/questions/53807205/yii2-restful-api-reason-cors-header-access-control-allow-origin-missing
        //return parent::behaviors();

        $behaviors['corsFilter'] = [
            'class' => '\yii\filters\Cors',
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
            ],
        ];

        return $behaviors;
       
    }

    public function actionIndex() {
        $o = (object) array("app"=>Yii::$app->name, "version"=>Yii::$app->params["apiVersion"], "endpoint"=>"sys");
        Yii::$app->api->sendSuccessResponse($o);
    }

    public function actionListSetting() {
        $d = (object)[];
        $d = Yii::$app->keyStorage->getAll([
            KeyStorageItem::APP_MAINTENANCE_MODE,
            KeyStorageItem::APP_MAINTENANCE_MESSAGE,
            KeyStorageItem::APP_ANNOUNCEMENT_MODE,
            KeyStorageItem::APP_ANNOUNCEMENT_MESSAGE,
            KeyStorageItem::APP_VERSION_ANDROID,
            KeyStorageItem::APP_VERSION_IOS,
            KeyStorageItem::APP_VERSION_ANDROID_DEPRECATE,
            KeyStorageItem::APP_VERSION_IOS_DEPRECATE,
        ]);
        Yii::$app->api->sendSuccessResponse($d);
    }

    public function actionDeviceAssessment(){
        if (!isset(Yii::$app->request->bodyParams['json'])) {
            \Yii::warning(Yii::$app->request->bodyParams, "actionDeviceAssessment bodyParams");
            throw new CustomHttpException(Utility::jsonifyError("json", Yii::t('common',"No json data.")), CustomHttpException::BAD_REQUEST);
        }

        $form = new DeviceAssessmentForm();
        $form->attributes = json_decode(Yii::$app->request->bodyParams['json'], true);
        $form->image_file = UploadedFile::getInstancesByName("image_file");

        if ($form->validate() && $pool = $form->assess()) {
            if ($pool) {
                //InstapPlanPool
                $data = [];
                $data['plan_pool_id'] = $pool->id;
                $data['policy_number'] = $pool->policy_number;
                $data['plan_status'] = $pool->plan_status;
                Yii::$app->api->sendSuccessResponse($data);
            }
        } 
        throw CustomHttpException::validationError($form); 
    }

    public function actionUploadPhotos() {
        if (isset($_POST['json'])) {
            $this->request = json_decode($_POST['json'], true);
        }
        $m = new UploadForm();
        $m->attributes = $this->request;
        $m->image_file = UploadedFile::getInstancesByName("image_file");

        if ($m->validate()) {
            $uploadAction = new \trntv\filekit\actions\UploadAction("uploads",$this);
            //loynote: update trntv\filekit to new version for uploadDirectory
            $uploadAction->uploadPath = "media/loytest";
            $uploadAction->fileparam = "image_file";
            $uploadAction->multiple = true;

            $data = [];

            $res = $uploadAction->run();
            $files = $res['files'];
            $files_count = count($files);
            for ($i=0; $i < $files_count; $i++) { 
                $temp = [
                    'base_url' => $files[$i]['base_url'],
                    'path' => $files[$i]['path']
                ];
                array_push($data, $temp);
            }

            Yii::$app->api->sendSuccessResponse($data);   
        } else {
            $str = $this->getSerialisedValidationError($m);
            throw new CustomHttpException($str, CustomHttpException::UNPROCESSABLE_ENTITY);
        }
    }

    public function actionTestEmail() {
        
        // Yii::$app->queue->delay(0)->push(new EmailQueueJob([
        //     'subject' => Yii::t('frontend', '{app-name} | Email Verification', ['app-name'=>"IP testing"]),
        //     'view' => 'verifyAccount',
        //     'to' => "ohjunwei951@gmail.com",
        //     'params' => [
        //         'user' => "ohjunwei951@gmail.com",
        //         'token' => "123"
        //     ]
        // ]));
        try {
            // Yii::$app->commandBus->handle(new SendEmailCommand([
            //     'subject' => "Testing",
            //     'view' => "verifyAccount",
            //     'to' => "ohjunwei951@gmail.com",
            //     'params' => [
            //         'user' => "ohjunwei951@gmail.com",
            //         'token' => "123"
            //     ],
            // ]));

        } catch(\Exception $e ) {
            print_r($e->getMessage());
        }
        
        
        // Yii::$app->rabbitMq->delay(0)->push("ccb");

        
    }

    public function actionTestMq() {
        /*
        Yii::$app->queue->delay(0)->push(new TestQueueJob([            
            'param1' => "foo",
            'param2' => "bar",
        ]));
        
        Yii::$app->rabbitMq->delay(0)->push("ccb");
        */


        $socket = SysSocketNotification::makeModel(SysSocketNotification::NOTIFY_SCAN_QR_PLAN_POOL, 3, "success");
        $str = "insert queue";
        if (!$socket->send()) {
            $str = "insert queue fail";
        } 
        return $str;
    }
    /*
    public function actionTestPost() {
        $str = "post:  ";
        $str .= file_get_contents('php://input');
        $str .= print_r($_REQUEST, true);
        return $str;
    }
    */
    
}
