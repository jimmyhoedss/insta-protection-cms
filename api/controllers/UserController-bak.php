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
use common\models\UserPlan;
use common\models\UserPlanAction;
use common\models\DealerCompany;
use common\models\DealerOrder;
use common\models\SysUserToken;
use common\models\SysOAuthAuthorizationCode;
use common\models\SysOAuthAccessToken;
use common\models\InstapPlanPool;
use common\models\UserFcmInbox;
use common\models\QcdClaimRegistration;
use common\models\QcdRepairCentre;
use common\models\UserCase;
use common\models\UserPlanDetail;

use common\models\form\LoginForm;
use common\models\form\UploadForm;
use common\models\form\RegistrationForm;
use common\models\form\UpdateProfileForm;
use common\models\form\RegisterFcmTokenForm;
use common\models\form\RegisterPlanForm;
use common\models\form\RegisterClaimForm;
use common\models\form\RegistrationResubmitForm;
use common\models\form\RegistrationResubmitClaimForm;
use common\models\form\OtpForm;
use common\models\form\AddPlanForm;
use common\models\form\ResendVerifyEmailForm;
use common\models\fcm\FcmCaseStatusChanged;
use common\models\fcm\FcmPlanStatusChanged;

use common\models\fcm\FcmOtp;
use common\components\Utility;
use common\components\MyCustomActiveRecord;

use api\behaviours\ApiAuth;
use api\behaviours\VerbCheck;
use api\components\HttpBearerAuth;
use api\components\CustomHttpException;

use common\commands\SendFcmCommand;
use trntv\bus\interfaces\SelfHandlingCommand;
use console\controllers\SysController;


class UserController extends RestControllerBase
{
    const MAX_ROW_PER_PAGE = 20;
    public $layout = false;

    public function behaviors() {
        return array_merge(parent::behaviors(), [
            
            'verbs' => [
                'class' => \yii\filters\VerbFilter::className(),
                'actions' => [
                    'index' => ['GET'],
                    // 'register' => ['POST'],
                    'logout' => ['GET'],
                    'get-otp' => ['POST'],
                    'verify-otp' => ['POST'],
                    'resend-verify-email' => ['GET'],
                    'verify-mobile' => ['POST'],
                    'authorize' => ['POST'],
                    'access-token' => ['POST'],
                    'me' => ['GET'],
                    'register-fcm-token' => ['POST'],
                    'register-plan' => ['POST'],
                    'plans' => ['GET'],
                    'add-plan' => ['POST'],
                    'my-plans' => ['GET'],
                    'my-plan-details' => ['POST'],
                    'my-plan-actions' => ['GET'],
                    'upload-photos' => ['POST'], 
                    'update-profile' => ['POST'], 
                    'registration-resubmit' => ['POST','GET'], 
                    'list-my-inbox' => ['GET'], 
                    'get-number-of-unread-notification' => ['GET'], 
                    'read-notification' => ['GET'], 
                    'favorite-notification' => ['GET'], 
                    'delete-notification' => ['GET'], 
                    'delete-all-notification' => ['GET'],
                    'refresh-provisional-token' => ['GET'],
                    'register-claim' => ['POST'], 
                    'registration-resubmit-claim' => ['POST','GET']
                ],
            ],
            'authenticator' => [
                'class' => HttpBearerAuth::className(),
                'except' => ['index', 'get-otp', 'verify-otp', 'access-token', 'upload-photos', 'test'],
            ],
            'ActiveTimestampBehavior' => [
                'class' => \common\behaviors\ActiveTimestampBehavior::className(),
                'attribute' => 'active_at'
            ],
        ]);       
    }
    
    public function actionIndex()  {
        $o = (object) array("app"=>Yii::$app->name, "version"=>Yii::$app->params["apiVersion"], "endpoint"=>"user");
        Yii::$app->api->sendSuccessResponse($o);
    }
    public function actionGetOtp()  {
        $form = new OtpForm();
        $form->attributes = $this->request;
        $form->mobile_number_full = $form->mobile_calling_code . $form->mobile_number;
        if ($form->validate())  {
            $user = $form->getOrRegisterUser();
            $form->sendSms($user, SysUserToken::TYPE_ONE_TIME_PASSWORD_API);
            if ($form->hasErrors()) {
                throw CustomHttpException::validationError($form);
            }
            $data = [];
            $data['message'] = 'OTP Sent';
            return Yii::$app->api->sendSuccessResponse($data);
        } else {
            throw CustomHttpException::validationError($form); 
        }
    }
    public function actionVerifyOtp()  {
        $form = new LoginForm();
        $form->scenario = LoginForm::API_LOGIN;
        $form->attributes = $this->request;

        if ($user = $form->loginApi()) {
            SysOAuthAuthorizationCode::deleteAllUserCodes($user);
            $auth_code = SysOAuthAuthorizationCode::makeModel($user);
            if ($auth_code->save()) {
                $data = [];
                $data['authorization_code'] = $auth_code->code;
                $data['expire_at'] = $auth_code->expire_at;
                Yii::$app->api->sendSuccessResponse($data);    
            } else {
                throw CustomHttpException::internalServerError(Yii::t('common',"Can't save auth code."));
            }
            
        } else {
            throw CustomHttpException::validationError($form);
        }
    }    
    public function actionAccessToken() {
        $authorization_code = isset($this->request["authorization_code"]) ? $this->request["authorization_code"] : null;
        if ($authorization_code == null) {
            throw new CustomHttpException(Utility::jsonifyError("authorization_code", Yii::t('common',"Missing authorization code."), CustomHttpException::KEY_INVALID_CREDENTIALS), CustomHttpException::UNPROCESSABLE_ENTITY);
        }
        $auth_code = SysOAuthAuthorizationCode::isValid($authorization_code);
        if (!$auth_code) {
            throw new CustomHttpException(Utility::jsonifyError("authorization_code", Yii::t('common',"Authorization code is invalid or has expired."), CustomHttpException::KEY_INVALID_CREDENTIALS), CustomHttpException::UNAUTHORIZED);
        }
        
        SysOAuthAuthorizationCode::deleteAllUserCodes($auth_code->user);
        SysOAuthAccessToken::deleteAllUserToken($auth_code->user);
        $accesstoken = SysOAuthAccessToken::makeModel($authorization_code, $auth_code->user);

        if ($accesstoken->save()) {
            $data = [];
            $data['access_token'] = $accesstoken->token;
            $data['expire_at'] = $accesstoken->expire_at;
            Yii::$app->api->sendSuccessResponse($data);
        } else {
            throw CustomHttpException::internalServerError(Yii::t('common',"Can't save access token."));
        }
    }
    public function actionMe() {
        $data = Yii::$app->user->identity->userDetails;
        Yii::$app->api->sendSuccessResponse($data);
    }
    public function actionRegisterFcmToken() {
        $model = new RegisterFcmTokenForm();
        $model->attributes = $this->request;

        if ($model->validate() && $model->registerFcmToken()) {
            Yii::$app->api->sendSuccessResponse(Yii::t('common',"New FCM token was saved."));
        } else {
            throw CustomHttpException::validationError($model);
        }
    }    
    public function actionAddPlan() {
        $form = new AddPlanForm();
        $form->channel = $this->request['channel'];
        $form->activation_token = $this->request['activation_token'];
        if ($form->validate() && $pool = $form->processOrder()) {
            if ($pool) {
                $data = [];
                $data['policy_number'] = $pool->policy_number;
                
                $fcm = new FcmPlanStatusChanged($pool);
                $fcm->send();

                Yii::$app->api->sendSuccessResponse($data);
                //set queue to notify socket io
            }
            
        } 
        throw CustomHttpException::validationError($form); 
    }
    public function actionMyPlans($status_type = InstapPlanPool::STATUS_TYPE_ALL, $page = 0, $pageSize = self::MAX_ROW_PER_PAGE) {
        $limit = ($pageSize > self::MAX_ROW_PER_PAGE) ? self::MAX_ROW_PER_PAGE : $pageSize; //page size
        $offset = $page * $limit;
        $user_id = Yii::$app->user->id;
        $status_type_arr = InstapPlanPool::planStatusType($status_type);
        $models = UserPlan::find()->active()
            ->planStatusInArr($status_type_arr)
            ->andWhere(['user_plan.user_id' => $user_id ])
            ->orderBy(['created_at'=>SORT_DESC])->limit($limit)->offset($offset)->all();

        $d = UserPlan::toObjectArray($models);
        Yii::$app->api->sendSuccessResponse($d);
    }
    public function actionMyPlanDetails() {
        $data = [];
        $user_id = Yii::$app->user->id;  
        $plan_pool_id_arr = $this->request["plan_pool_ids"];  
        $user_plan_id_arr = UserPlan::getMatchedPlanPoolIdByUser($plan_pool_id_arr, $user_id);
        if(!empty($user_plan_id_arr)) {
            $userPlans = UserPlan::find()->where(['in', 'plan_pool_id', $user_plan_id_arr])->andWhere(['user_id' => $user_id])->all();
            foreach ($userPlans as $userPlan) {
                if($userPlan) {
                    array_push($data, $userPlan->allPlanDetailObject());
                }
            }
        } else {
            $str =  Utility::jsonifyError("plan_pool_id", "Not authorized to get plan detail.");
            throw new CustomHttpException($str, CustomHttpException::UNAUTHORIZED);
        }
        Yii::$app->api->sendSuccessResponse($data);

    }
    public function actionMyPlanActions($plan_pool_id) {
        $models = UserPlanAction::find()->active()->Where(['plan_pool_id'=>$plan_pool_id])->orderBy(['created_at'=>SORT_DESC])->all();

        $d = UserPlanAction::toObjectArray($models);
        Yii::$app->api->sendSuccessResponse($d);
    }
    public function actionRegisterPlan() {
        if (!isset($_POST['json'])) {
            throw new CustomHttpException(Utility::jsonifyError("json", Yii::t('common',"No json data.")), CustomHttpException::BAD_REQUEST);
        }
        $form = new RegisterPlanForm();
        $form->scenario = RegisterPlanForm::SCENARIO_BOTH;
        $form->attributes = json_decode($_POST['json'], true);
        $form->image_file = UploadedFile::getInstancesByName("image_file");

        if ($form->validate() && $pool = $form->registerPlan()) {
            if ($pool) {
                //InstapPlanPool
                $data = [];
                $data['plan_pool_id'] = $pool->id;
                $data['policy_number'] = $pool->policy_number;
                $data['plan_status'] = $pool->plan_status;
                $fcm = new FcmPlanStatusChanged($pool);
                $fcm->send();
                Yii::$app->api->sendSuccessResponse($data);
            }

        } 
        throw CustomHttpException::validationError($form); 
    }
    public function actionRegistrationResubmit(){
        if (!isset($_POST['json'])) {
            throw new CustomHttpException(Utility::jsonifyError("json", Yii::t('common',"No json data.")), CustomHttpException::BAD_REQUEST);
        }
        $form = new RegistrationResubmitForm();
        $form->plan_pool_id = json_decode($_POST['json'], true)['plan_pool_id'];
        $hasDetail = isset(json_decode($_POST['json'], true)['description']);
        $hasPhoto = count($_FILES) > 0;
        $scenario=null;
        if (!$hasDetail && !$hasPhoto) {
            throw new CustomHttpException(Utility::jsonifyError("json", Yii::t('common',"No json data or image file.")), CustomHttpException::BAD_REQUEST);
        }
        if ($hasDetail) {
            $form->scenario = RegistrationResubmitForm::SCENARIO_DETAIL;
            $form->description = json_decode($_POST['json'], true)['description'];
        }
        if ($hasPhoto) {
            $form->scenario = RegistrationResubmitForm::SCENARIO_PHOTO;
            $form->image_file = UploadedFile::getInstancesByName("image_file");
        }
        if ($hasDetail && $hasPhoto) {
            $form->scenario = RegistrationResubmitForm::SCENARIO_BOTH;
        }

        if ($form->validate() && $pool = $form->resubmit()) {
            if ($pool) {
                //InstapPlanPool
                $data = [];
                $data['plan_pool_id'] = $pool->id;
                $data['policy_number'] = $pool->policy_number;
                $data['plan_status'] = $pool->plan_status;
                $fcm = new FcmPlanStatusChanged($pool);
                $fcm->send();
                Yii::$app->api->sendSuccessResponse($data);
            }
        }
        throw CustomHttpException::validationError($form);
    }
    public function actionUpdateProfile(){
        $form = new UpdateProfileForm();
        $hasDetail = isset($_POST['detail']);
        $hasPhoto = count($_FILES) > 0;
        $scenario=null;
        if (!$hasDetail && !$hasPhoto) {
            throw new CustomHttpException(Utility::jsonifyError("detail", Yii::t('common',"No detail or image file.")), CustomHttpException::BAD_REQUEST);
        }
        if ($hasDetail) {
            $form->scenario = UpdateProfileForm::SCENARIO_DETAIL;
            $form->attributes = json_decode($_POST['detail'], true);
        }
        if ($hasPhoto) {
            $form->scenario = UpdateProfileForm::SCENARIO_PHOTO;
            $form->image_file = UploadedFile::getInstancesByName("image_file");
        }
        if ($hasDetail && $hasPhoto) {
            $form->scenario = UpdateProfileForm::SCENARIO_BOTH;
        }

        if ($form->validate() && $form->update()) {
            $data = Yii::$app->user->identity->userDetails;
            Yii::$app->api->sendSuccessResponse($data);
        }
        throw CustomHttpException::validationError($form);
    }
    public function actionLogout() {
        try{
            $user = Yii::$app->user->identity;
            User::revokeAccessToken($user->id);
            User::revokeFcmToken($user->id);
            Yii::$app->api->sendSuccessResponse(["Logged Out Successfully"]);
        } catch (yii\db\IntegrityException $e) {
            throw new CustomHttpException(Utility::jsonifyError("", Yii::t('common',"Your request was made with invalid credentials.")), CustomHttpException::UNAUTHORIZED, CustomHttpException::KEY_INVALID_CREDENTIALS);
        } catch ( \Exception $e ) {
            throw new CustomHttpException(Utility::jsonifyError("", Yii::t('common',"Your request was made with invalid credentials.")), CustomHttpException::UNAUTHORIZED, CustomHttpException::KEY_INVALID_CREDENTIALS);
        }
    }
    public function actionListMyInbox($page = 0, $pageSize = self::MAX_ROW_PER_PAGE) {
        $limit = ($pageSize > self::MAX_ROW_PER_PAGE) ? self::MAX_ROW_PER_PAGE : $pageSize; //page size
        $offset = $page * $limit;

        $user_id = Yii::$app->user->id;
        
        $m = UserFcmInbox::find()->Where(['user_id' => $user_id])->orderBy(['status_favorite'=> SORT_ASC, 'created_at'=>SORT_DESC])->limit($limit)->offset($offset)->all();
        $data = UserFcmInbox::toObjectArray($m);
        Yii::$app->api->sendSuccessResponse($data);
    }
    public function actionReadNotification($id = null){
        if ($id == null) {
            throw new CustomHttpException(Utility::jsonifyError("id", Yii::t('common',"No id.")), CustomHttpException::BAD_REQUEST);
        }
        $m = UserFcmInbox::find()->Where(['id' => $id])->andWhere(['user_id' => Yii::$app->user->id])->one();
        if($m){
            $m->updateAttributes(['status_read' => true]);
            Yii::$app->api->sendSuccessResponse(Yii::t('common',"Message Read"));
        } else {
            throw new CustomHttpException(Utility::jsonifyError("id", Yii::t('common',"Invalid ID.")), CustomHttpException::FORBIDDEN);
        }
    }
    public function actionFavoriteNotification($id = null){
        if ($id == null) {
            throw new CustomHttpException(Utility::jsonifyError("id", Yii::t('common',"No id.")), CustomHttpException::BAD_REQUEST);
        }
        $m = UserFcmInbox::find()->Where(['id' => $id])->andWhere(['user_id' => Yii::$app->user->id])->one();
        if($m){
            $m->updateAttributes(['status_favorite' => $m->status_favorite == 'true' ? 'false' : 'true']);
            Yii::$app->api->sendSuccessResponse(Yii::t('common',"Message Favorited"));
        } else {
            throw new CustomHttpException(Utility::jsonifyError("id", Yii::t('common',"Invalid ID.")), CustomHttpException::FORBIDDEN);
        }
    }
    public function actionDeleteNotification($id = null){
        if ($id == null) {
            throw new CustomHttpException(Utility::jsonifyError("id", Yii::t('common',"No id.")), CustomHttpException::BAD_REQUEST);
        }
        $m = UserFcmInbox::find()->Where(['id' => $id])->andWhere(['user_id' => Yii::$app->user->id])->one();
        if($m && $m->delete()){
            Yii::$app->api->sendSuccessResponse(Yii::t('common',"Message Deleted"));
        } else {
            throw new CustomHttpException(Utility::jsonifyError("id", Yii::t('common',"Invalid ID.")), CustomHttpException::FORBIDDEN);
        }
    }
    public function actionDeleteAllNotification(){
        if(UserFcmInbox::deleteAll(['user_id' => Yii::$app->user->id])){
            Yii::$app->api->sendSuccessResponse(Yii::t('common',"All Messages Deleted"));
        } else {
            throw new CustomHttpException(Utility::jsonifyError("user_id", Yii::t('common',"Nothing to delete.")), CustomHttpException::FORBIDDEN);
        }
    }
    public function actionGetNumberOfUnreadNotification() {
        $m = new UserFcmInbox();
        $m->user_id = Yii::$app->user->id;
        Yii::$app->api->sendSuccessResponse((int) $m->numberOfUnreadMessages);
    }
    public function actionResendVerifyEmail() {
        $model = new ResendVerifyEmailForm();
        $model->email = Yii::$app->user->identity->email;
        $model->user_id = Yii::$app->user->id;

        if ($model->validate() && $model->resendEmail()) {
            $data['message'] = Yii::t('common',"Account verification email sent.");
            Yii::$app->api->sendSuccessResponse($data);
        } else {
            $str = $this->getSerialisedValidationError($model);
            throw new CustomHttpException($str, CustomHttpException::UNPROCESSABLE_ENTITY);
        }
    }
    public function actionRefreshProvisionalToken() {
        try {
            $user = Yii::$app->user->identity;
            $newToken = User::generateProvisionalToken();
            $user->updateAttributes(['provisional_token' => $newToken]);
            Yii::$app->api->sendSuccessResponse(['provisional_token' => $newToken]);            
        } catch (yii\db\IntegrityException $e) {
            throw CustomHttpException::internalServerError(Yii::t('common',"Cannot update provisional_token."));
        } catch ( \Exception $e ) {
            throw CustomHttpException::internalServerError(Yii::t('common',"Cannot update provisional_token."));
        }
    }
    public function actionRegisterClaim() {
        if (!isset($_POST['json'])) {
            throw new CustomHttpException(Utility::jsonifyError("json", Yii::t('common',"No json data.")), CustomHttpException::BAD_REQUEST);
        }
        $form = new RegisterClaimForm();
        $form->attributes = json_decode($_POST['json'], true);
        $form->image_file = UploadedFile::getInstancesByName("image_file");
        // print_r($form->validate());
        // exit();
        if ($form->validate() && $case = $form->registerClaim()) {
            if ($case) {
                //InstapPlanPool
                $data = [];
                $data['case_id'] = $case->id;
                $data['policy_number'] = $case->planPool->policy_number;
                $data['current_case_status'] = $case->current_case_status;
                $fcm = new FcmCaseStatusChanged($case);
                $fcm->send();
                Yii::$app->api->sendSuccessResponse($data);
            }
            
        } 
        throw CustomHttpException::validationError($form); 
    }
    public function actionRegistrationResubmitClaim(){
        if (!isset($_POST['json'])) {
            throw new CustomHttpException(Utility::jsonifyError("json", Yii::t('common',"No json data.")), CustomHttpException::BAD_REQUEST);
        }
        $form = new RegistrationResubmitClaimForm();
        $form->plan_pool_id = json_decode($_POST['json'], true)['plan_pool_id'];
        $hasDetail = isset(json_decode($_POST['json'], true)['description']);
        $hasPhoto = count($_FILES) > 0;
        $scenario=null;
        if (!$hasDetail && !$hasPhoto) {
            throw new CustomHttpException(Utility::jsonifyError("json", Yii::t('common',"No json data or image file.")), CustomHttpException::BAD_REQUEST);
        }
        if ($hasDetail) {
            $form->scenario = RegistrationResubmitClaimForm::SCENARIO_DETAIL;
            $form->description = json_decode($_POST['json'], true)['description'];
        }
        if ($hasPhoto) {
            $form->scenario = RegistrationResubmitClaimForm::SCENARIO_PHOTO;
            $form->image_file = UploadedFile::getInstancesByName("image_file");
        }
        if ($hasDetail && $hasPhoto) {
            $form->scenario = RegistrationResubmitClaimForm::SCENARIO_BOTH;
        }

        if ($form->validate() && $case = $form->resubmit()) {
            if ($case) {
                //InstapPlanPool
                $data = [];
                $data['case_id'] = $case->id;
                $data['policy_number'] = $case->planPool->policy_number;
                $data['current_case_status'] = $case->current_case_status;
                $fcm = new FcmCaseStatusChanged($case);
                $fcm->send();
                Yii::$app->api->sendSuccessResponse($data);
            }
        }
        throw CustomHttpException::validationError($form);
    }

    /*public function actionTest(){
        // remind user about plan pending registeration for 3 days
        try {
            $pendingRegPolicies = InstapPlanPool::find()->where(['plan_status'=> InstapPlanPool::STATUS_PENDING_REGISTRATION])->andWhere(['and', 'updated_at <= updated_at + 259200'])->all();
            if(!empty($pendingRegPolicies)){
                foreach ($pendingRegPolicies as $policy) {
                    $fcm = new FcmPlanStatusChanged($policy);
                    $fcm->send();
                }
            }
        } catch (Exception $e) {
            User::sendTelegramBotMessage("Failed to send reminder notification about plan prending registeration");
            User::sendTelegramBotMessage(json_encode($e));
        }

        // remind user about plan require clarification for 3 days
        try {
            $pendingClarifyPolicies = InstapPlanPool::find()->where(['plan_status'=> InstapPlanPool::STATUS_REQUIRE_CLARIFICATION])->andWhere(['and', 'updated_at <= updated_at + 259200'])->all();
            if(!empty($pendingClarifyPolicies)){
                foreach ($pendingClarifyPolicies as $policy) {
                    $fcm = new FcmPlanStatusChanged($policy);
                    $fcm->send();
                }
            }
        } catch (Exception $e) {
            User::sendTelegramBotMessage("Failed to send reminder notification about plan require clarification");
            User::sendTelegramBotMessage(json_encode($e));
        }

        // remind user about claim require clarification for 3 days
        try {
            $pendingClarifyCase = UserCase::find()->where(['current_case_status'  => UserCase::CASE_STATUS_CLAIM_REQUIRE_CLARIFICATION])->andWhere(['and', 'updated_at <= updated_at + 259200'])->all();
            if(!empty($pendingClarifyCase)){
                foreach ($pendingClarifyCase as $case) {
                    $fcm = new FcmCaseStatusChanged($case);
                    $fcm->send();
                }
            }
        } catch (Exception $e) {
            User::sendTelegramBotMessage("Failed to send reminder notification about claim require clarification");
            User::sendTelegramBotMessage(json_encode($e));
        }
    }*/

}