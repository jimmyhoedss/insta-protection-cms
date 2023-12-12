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
use common\models\InstapPlan;
use common\models\InstapPlanPool;
use common\models\UserPlanAction;
use common\models\RbacAuthAssignment;

use common\models\DealerCompany;
use common\models\DealerUser;
use common\models\DealerOrder;
use common\models\DealerOrderAdHoc;
use common\models\DealerUserHistory;
use common\models\DealerCompanyDealer;
use common\models\DealerOrderInventory;
use common\models\DealerOrderInventoryOverview;
use common\models\DealerInventoryAllocationHistory;
use common\models\InstapPlanDealerCompany;
use common\rbac\rule\OwnOrganizationlRule;
use common\matchcallback\DealerMatchCallBack;

use common\components\Utility;
use common\components\MyCustomActiveRecord;


use common\models\fcm\FcmDealerAddStaff;
use common\models\fcm\FcmDealerDeleteStaff;
use common\models\fcm\FcmPlanStatusChanged;
use common\models\form\RegisterPlanForm;
use common\models\form\UpdateCompanyProfileForm;
use common\models\form\DealerUserForm;


use api\behaviours\ApiAuth;
use api\behaviours\VerbCheck;
use api\components\HttpBearerAuth;
use api\components\CustomHttpException;


class DealerCompanyController extends RestControllerBase
{
    const MAX_ROW_PER_PAGE = 20;
    public $layout = false;

    public function behaviors() {
        return array_merge(parent::behaviors(), [
            
            'verbs' => [
                'class' => \yii\filters\VerbFilter::className(),
                'actions' => [
                    'index' => ['GET'],
                    'order-plan-ad-hoc' => ['GET'],
                    'order-plan-stockpile' => ['GET'],
                    'available-plans' => ['GET'],
                    'available-plans-by-category' => ['GET'],
                    'me' => ['GET'],
                    'add-staff' => ['GET'],
                    'view-staff' => ['GET','POST'],
                    'delete-staff' => ['GET'],
                    'void-order' => ['GET'],
                    'order-history' => ['GET'],
                    'order-history-by-category' => ['GET'],
                    'company-order-history' => ['GET'],
                    'company-order-history-by-category' => ['GET'],
                    'order-history-graph' => ['GET'],
                    'order-history-graph-by-category' => ['GET'],
                    'company-order-history-graph' => ['GET'],
                    'company-order-history-graph-by-category' => ['GET'],
                    'register-plan-photo' => ['POST'],
                    'staff-movement' => ['GET'],
                    'update-profile' => ['POST'],
                    'view-info' => ['POST'],
                    'organization-chart' => ['GET'],
                    'plan-detail' => ['POST']
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
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                       'actions' => ['me', 'order-plan-ad-hoc', 'order-plan-stockpile', 'available-plans', 'available-plans-by-category', 'add-staff', 'void-order', 'order-history', 'order-history-graph', 'register-plan-photo','update-profile','organization-chart','plan-detail','view-info', 'order-history-graph-by-category', 'order-history-by-category'],
                       'allow' => true,
                       'roles' => [User::ROLE_DEALER_ASSOCIATE],
                    ],
                    [  'actions' => ['me', 'order-plan-ad-hoc', 'order-plan-stockpile', 'available-plans', 'available-plans-by-category','add-staff', 'view-staff', 'delete-staff', 'void-order', 'company-order-history', 'order-history', 'company-order-history-graph', 'order-history-graph','register-plan-photo', 'staff-movement','update-profile','view-info', 'company-order-history-by-category', 'order-history-graph-by-category', 'order-history-by-category', 'company-order-history-graph-by-category'],
                       'allow' => true,
                       'roles' => [User::ROLE_DEALER_MANAGER],
                    ], 

                    // [
                    //    'actions' => ['available-plans'],
                    //    'allow' => true,
                    //    'roles' => [User::ROLE_USER],
                    // ],

                ],
            ],
        ]);       
    }

    public function actionIndex()  {
        $o = (object) array("app"=>Yii::$app->name, "version"=>Yii::$app->params["apiVersion"], "endpoint"=>"dealer-company");
        Yii::$app->api->sendSuccessResponse($o);
    }

    public function actionMe() {
        $dealer = DealerUser::getDealerFromUserId(Yii::$app->user);
        if ($dealer) {
            Yii::$app->api->sendSuccessResponse($dealer->toObject());
        }
        throw CustomHttpException::internalServerError("Error getting dealer data.");
    }    
    //api to support app version before re-skining 
    public function actionAvailablePlans() {
        // $offset = $page * $limit;
        $user = Yii::$app->user;
        $dealer_company = DealerUser::getDealerFromUserId($user);
        $dealer_company_id = $dealer_company->id;
        $region_id = $user->identity->region_id;
        $company_plan = DealerCompany::companyPlans($dealer_company_id, $region_id);
        $data = InstapPlan::toObjectArray($company_plan);

        Yii::$app->api->sendSuccessResponse($data);
    }
    // api for re-skining 
    public function actionAvailablePlansByCategory($category = InstapPlan::ALL_CATEGORY, $tier = InstapPlan::ALL_TIER) {
        // $offset = $page * $limit;
        //filter to bool
        if(isset($_GET['category'])) { 
                if(!in_array($_GET['category'], array_keys(InstapPlan::allPlanCategory()))) {
                   throw new CustomHttpException(Utility::jsonifyError("category", Yii::t("common", "Invalid category.")), CustomHttpException::BAD_REQUEST);
                }
           } else {
             throw new CustomHttpException(Utility::jsonifyError("category", Yii::t("common", "Missing category.")), CustomHttpException::BAD_REQUEST);
           }
        if(isset($_GET['tier'])){
            if(!in_array($_GET['tier'], array_keys(InstapPlan::allPlanTier()))) {
                throw new CustomHttpException(Utility::jsonifyError("tier", Yii::t("common","Invalid tier.")), CustomHttpException::BAD_REQUEST);
            }
        }
        $user = Yii::$app->user;
        $dealer_company = DealerUser::getDealerFromUserId($user);
        $dealer_company_id = $dealer_company->id;
        $region_id = $user->identity->region_id;
        $company_plan = DealerCompany::plansByCategory($dealer_company_id, $region_id, $category, $tier);
        $data = InstapPlan::categorizeByTier($company_plan);

        // if($groupByTier) {
        //     $data = InstapPlan::categorizeByTier($company_plan);
        // } else {
        //     $data = InstapPlan::toObjectArray($company_plan);
        // }

        Yii::$app->api->sendSuccessResponse($data);
    }

    public function actionOrderPlanAdHoc($plan_id)  {
        $dealer = DealerUser::getDealerFromUserId(Yii::$app->user);
        $region_id = Yii::$app->user->identity->region_id;

        if ($dealer->sp_inventory_order_mode != DealerCompany::INVENTORY_MODE_AD_HOC) {
            $str= Utility::jsonifyError("plan_id", Yii::t("common","Invalid order mode."));
            throw new CustomHttpException($str, CustomHttpException::UNPROCESSABLE_ENTITY);
        }
        //check is plan available for company
        $company_plan = DealerCompany::companyPlans($dealer->id, $region_id);
        if($company_plan) {
            $plan_id_arr = array_column($company_plan, 'id');
            if(!in_array($plan_id, $plan_id_arr)) {
                $str= Utility::jsonifyError("plan_id", Yii::t("common","Plan not available in this company."));
                throw new CustomHttpException($str, CustomHttpException::UNPROCESSABLE_ENTITY);
            }
        } else {
            $str= Utility::jsonifyError("plan_id", Yii::t('common', "Plan not found"));
            throw new CustomHttpException($str, CustomHttpException::UNPROCESSABLE_ENTITY);
        }
        $plan = InstapPlan::find()->andWhere(['id' => $plan_id])->active()->one(); // ->active()
        // if not null
        if($plan) {
            DealerOrderAdHoc::deleteAllOrder(Yii::$app->user, $plan);
            $order = DealerOrderAdHoc::makeModel(Yii::$app->user, $plan, $dealer);
            
            if ($order->save()) {
                $data = [];
                $data['plan_id'] = $plan->id;
                $data['plan_sku'] = $plan->sku;
                $data['plan_name'] = $plan->name;
                $data['channel'] = InstapPlan::SALES_CHANNEL_DEALER_TYPE1;
                $data['activation_token'] = $order->activation_token;
                $data['expire_at'] = $order->expire_at;
                Yii::$app->api->sendSuccessResponse($data);
            } else {
                $str = $this->getSerialisedValidationError($order);
            }
        }
        $str= Utility::jsonifyError("plan_id", "Invalid plan_id.");
        throw new CustomHttpException($str, CustomHttpException::UNPROCESSABLE_ENTITY);

    }
    public function actionOrderPlanStockpile($plan_id)  {
        $str="";
        $dealerCompany = DealerUser::getDealerFromUserId(Yii::$app->user);
        $region_id = Yii::$app->user->identity->region_id;

        if ($dealerCompany->sp_inventory_order_mode != DealerCompany::INVENTORY_MODE_STOCKPILE) {
            $str= Utility::jsonifyError("plan_id", Yii::t("common","Invalid order mode."));
            throw new CustomHttpException($str, CustomHttpException::UNPROCESSABLE_ENTITY);
        }
        //check is plan available for company
        $company_plan = DealerCompany::companyPlans($dealerCompany->id, $region_id);
        if($company_plan) {
            $plan_id_arr = array_column($company_plan, 'id');
            if(!in_array($plan_id, $plan_id_arr)) {
                $str= Utility::jsonifyError("plan_id", Yii::t("common","Plan not available in this company."));
                throw new CustomHttpException($str, CustomHttpException::UNPROCESSABLE_ENTITY);
            }
        } else {
            $str= Utility::jsonifyError("plan_id", Yii::t('common', "Plan not found"));
            throw new CustomHttpException($str, CustomHttpException::UNPROCESSABLE_ENTITY);
        }
        $plan = InstapPlan::find()->andWhere(['id' => $plan_id])->active()->one(); // ->active()
        // if not null
        if($plan){
            $stock = DealerOrderInventory::retrieveAvailableStock($plan, $dealerCompany);
            $dealer_user_id = Yii::$app->user->id;
            if($stock) {
                $stock->activation_token = Utility::randomToken(64);
                $stock->expire_at = time() + 60;
                $stock->dealer_user_id = $dealer_user_id;

                if($stock->save()) {
                    $data = [];
                    $data['plan_id'] = $plan->id;
                    $data['plan_sku'] = $plan->sku;
                    $data['plan_name'] = $plan->name;
                    $data['channel'] = InstapPlan::SALES_CHANNEL_DEALER_TYPE2;
                    $data['activation_token'] = $stock->activation_token;
                    $data['expire_at'] = $stock->expire_at;
                    Yii::$app->api->sendSuccessResponse($data);
                } else {
                     $str= Utility::jsonifyError("plan_id", Yii::t('common', "Error generate stock"));
                }
            } else {
                $str= Utility::jsonifyError("plan_id", Yii::t('common', "No more stock")); 
            }
        }else{
            $str= Utility::jsonifyError("plan_id", "Invalid plan id.");
        }
        throw new CustomHttpException($str, CustomHttpException::UNPROCESSABLE_ENTITY);
    }

    public function actionAddStaff($dealer_staff_mobile) {

        $form = new DealerUserForm();
        $form->scenario = DealerUserForm::SCENARIO_ADD_STAFF;
        $form->dealer_staff_mobile = $dealer_staff_mobile;
        //sent fcm to all the dealer manager 
        if ($form->validate() && $staff = $form->addStaff()) {
            if($staff) {
                // Yii::$app->api->sendSuccessResponse($staff);
                Yii::$app->api->sendSuccessResponse($staff->toObject());
            }
        }else {
            throw CustomHttpException::validationError($form); 
        }
        
    }

    public function actionViewStaff($page = 0, $pageSize = self::MAX_ROW_PER_PAGE){
        $limit = ($pageSize > self::MAX_ROW_PER_PAGE) ? self::MAX_ROW_PER_PAGE : $pageSize; //page size
        $offset = $page * $limit;
        $company_id_arr = $this->request["company_ids"];
        $dealer_company = DealerUser::getDealerFromUserId(Yii::$app->user);
        $flag = DealerCompany::isSameLinearOrganisation($dealer_company->id, $company_id_arr);
        if ($flag) {
            $dealerStaff = DealerUser::find()->where(['in', 'dealer_company_id', $company_id_arr])->limit($limit)->offset($offset)->orderBy(['created_at' => SORT_DESC])->active()->all();
            $ds = DealerUser::toObjectArray($dealerStaff);
            Yii::$app->api->sendSuccessResponse($ds);
        } else {
            $str =  Utility::jsonifyError("dealer_company_ids", Yii::t("common", "Not authorized company ids."));
            throw new CustomHttpException($str, CustomHttpException::UNAUTHORIZED);
        }

    }

    public function actionDeleteStaff($dealer_staff_id) {

        $form = new DealerUserForm();
        $form->scenario = DealerUserForm::SCENARIO_DELETE_STAFF;
        $form->dealer_staff_id = $dealer_staff_id;
        //sent fcm to all the dealer manager 
        if ($form->validate() && $form->deleteStaff() != null) {
             $data = [];
             $data['message'] = Yii::t('common', 'Staff deleted');
            Yii::$app->api->sendSuccessResponse($data);
        }else {
            throw CustomHttpException::validationError($form); 
        }
        
    }
 
    public function actionVoidOrder($plan_pool_id, $reason){
        $planPool = InstapPlanPool::find()->andWhere(['id'=>$plan_pool_id])->one();
        if ($planPool) {
            if ($planPool->plan_status != InstapPlanPool::STATUS_PENDING_REGISTRATION) {
                $str= Utility::jsonifyError("plan_pool_id", Yii::t("common", "unable to void."), CustomHttpException::KEY_INVALID_CREDENTIALS);
                throw new CustomHttpException($str, CustomHttpException::BAD_REQUEST);
            }
            $timeDifference = (date_timestamp_get(date_create()) - $planPool->created_at)/60; //in mins
            if ($timeDifference > 15) {
                $str= Utility::jsonifyError("plan_pool_id", Yii::t("common","Plan pool was created more than 15 minutes, unable to void now."), CustomHttpException::KEY_INVALID_CREDENTIALS);
                throw new CustomHttpException($str, CustomHttpException::BAD_REQUEST);
            }
            $transaction = Yii::$app->db->beginTransaction();
            $m = UserPlanAction::makeModel($planPool, UserPlanAction::ACTION_CANCEL, $reason);
            try {                
                if ($m->save()) {

                    $planPool->updateAttributes(["plan_status"=>InstapPlanPool::STATUS_CANCEL]);
                    $transaction->commit();
                    $fcm = new FcmPlanStatusChanged($planPool);
                    $fcm->send();
                    $msg =  Yii::t("common", "Order voided!");
                    Yii::$app->api->sendSuccessResponse($msg);
                } else {
                    throw CustomHttpException::internalServerError(Yii::t("common", "Cannot update plan detail."));
                }
            } catch (yii\db\IntegrityException $e) {
                $transaction->rollback();
                throw CustomHttpException::internalServerError("Cannot void plan.");
            }
        } else {
            $str= Utility::jsonifyError("plan_pool_id", Yii::t("common", "Plan pool does not exist."), CustomHttpException::KEY_INVALID_CREDENTIALS);
            throw new CustomHttpException($str, CustomHttpException::BAD_REQUEST);
        }
    }
    //this function need to remain to support app before reskinning
    //toDo: delete this after we force all user to update app
    public function actionCompanyOrderHistory($page = 0, $pageSize = self::MAX_ROW_PER_PAGE){
        $limit = ($pageSize > self::MAX_ROW_PER_PAGE) ? self::MAX_ROW_PER_PAGE : $pageSize; //page size
        $offset = $page * $limit;
        $dealer = DealerUser::getDealerFromUserId(Yii::$app->user);
        $dealerOrder = DealerOrder::find()->andWhere(['dealer_company_id'=>$dealer->id])->limit($limit)->offset($offset)->orderBy(['created_at'=>SORT_DESC])->all();
        $do = DealerOrder::toObjectArray($dealerOrder);
        Yii::$app->api->sendSuccessResponse($do);
    }
    //for app after re-skinning
    public function actionCompanyOrderHistoryByCategory($page = 0, $pageSize = self::MAX_ROW_PER_PAGE, $category = InstapPlan::ALL_CATEGORY){
        if(isset($_GET['category'])) { 
            if(!in_array($_GET['category'], array_keys(InstapPlan::allPlanCategory()))) {
               throw new CustomHttpException(Utility::jsonifyError("category", Yii::t("common", "Invalid category.")), CustomHttpException::BAD_REQUEST);
            }
        }else {
            throw new CustomHttpException(Utility::jsonifyError("category", Yii::t("common", "category cannot be empty.")), CustomHttpException::BAD_REQUEST);
        }
        $limit = ($pageSize > self::MAX_ROW_PER_PAGE) ? self::MAX_ROW_PER_PAGE : $pageSize; //page size
        $offset = $page * $limit;
        $dealer = DealerUser::getDealerFromUserId(Yii::$app->user);
        $dealerOrder = DealerOrder::find()->byCategory($category)->andWhere(['dealer_order.dealer_company_id'=>$dealer->id])->limit($limit)->offset($offset)->orderBy(['dealer_order.created_at'=>SORT_DESC])->all();
        // ->createCommand()->getRawSql();
        // print_r($dealerOrder);exit();
        $do = DealerOrder::toObjectArray($dealerOrder);
        Yii::$app->api->sendSuccessResponse($do);
    }
    //this function need to remain to support app before reskinning
    //toDo: delete this after we force all user to update app
    public function actionOrderHistory($dealer_user_id = null, $page = 0, $pageSize = self::MAX_ROW_PER_PAGE){
        $limit = ($pageSize > self::MAX_ROW_PER_PAGE) ? self::MAX_ROW_PER_PAGE : $pageSize; //page size
        $offset = $page * $limit;

        $dealer = DealerUser::getDealerFromUserId(Yii::$app->user);
        $dealerOrder = DealerOrder::find()->andWhere(['dealer_company_id'=>$dealer->id]);
        if($dealer_user_id){
            $dealerOrder = $dealerOrder->andWhere(['dealer_user_id' => $dealer_user_id]);
        } else {
            $dealerOrder = $dealerOrder->andWhere(['dealer_user_id' => Yii::$app->user->identity->id]);
        }
        $dealerOrder = $dealerOrder->orderBy(['created_at'=>SORT_DESC])->limit($limit)->offset($offset)->all();

        $do = DealerOrder::toObjectArray($dealerOrder);
        Yii::$app->api->sendSuccessResponse($do);
    }
    //use for app after reskinning
    public function actionOrderHistoryByCategory($dealer_user_id = null, $page = 0, $pageSize = self::MAX_ROW_PER_PAGE,  $category = InstapPlan::ALL_CATEGORY){
        if(isset($_GET['category'])) { 
            if(!in_array($_GET['category'], array_keys(InstapPlan::allPlanCategory()))) {
               throw new CustomHttpException(Utility::jsonifyError("category", Yii::t("common", "Invalid category.")), CustomHttpException::BAD_REQUEST);
           }
        }else {
            throw new CustomHttpException(Utility::jsonifyError("category", Yii::t("common", "category cannot be empty.")), CustomHttpException::BAD_REQUEST);
        }
        $limit = ($pageSize > self::MAX_ROW_PER_PAGE) ? self::MAX_ROW_PER_PAGE : $pageSize; //page size
        $offset = $page * $limit;

        $dealer = DealerUser::getDealerFromUserId(Yii::$app->user);
        $dealerOrder = DealerOrder::find()->byCategory($category)->andWhere(['dealer_order.dealer_company_id'=>$dealer->id]);
        if($dealer_user_id){
            $dealerOrder = $dealerOrder->andWhere(['dealer_order.dealer_user_id' => $dealer_user_id]);
        } else {
            $dealerOrder = $dealerOrder->andWhere(['dealer_order.dealer_user_id' => Yii::$app->user->identity->id]);
        }
        $dealerOrder = $dealerOrder->orderBy(['dealer_order.created_at'=>SORT_DESC])->limit($limit)->offset($offset)->all();

        $do = DealerOrder::toObjectArray($dealerOrder);
        Yii::$app->api->sendSuccessResponse($do);
    }
    //this function need to remain to support app before reskinning
    //toDo: delete this after we force all user to update app
    public function actionCompanyOrderHistoryGraph($day){
        //query check for time
        //SELECT count(*),FROM_UNIXTIME(created_at, '%m-%d-%Y') as d FROM `dealer_order` WHERE dealer_company_id = 68 group by d ORDER BY `created_at`  DESC
        date_default_timezone_set('Asia/Singapore');
        $dealer = DealerUser::getDealerFromUserId(Yii::$app->user);
        $a = array();
        //toDo: need to change the time zone base on country
        for($i=0;$i<$day;$i++){
            if ($i == 0) { 
                $start = strtotime("today midnight 00:00:00");
                $end = strtotime("today midnight 23:59:59");
            } else {
                $start = strtotime("today -".$i."day midnight 00:00:00");
                $end = strtotime("today -".$i."day midnight 23:59:59"); 
            }
            $dealerOrder = DealerOrder::find()->andWhere(['dealer_company_id'=>$dealer->id])->andWhere(['>=', 'created_at', $start])->andWhere(['<=', 'created_at', $end])->count();
            $o = (object) []; 
            $d = date("d/m", $start);
            $o->$d = $dealerOrder;
            array_push($a,$o);
        }
        $a = array_reverse($a);
        Yii::$app->api->sendSuccessResponse($a);
    }
    //use for app after reskinning
    public function actionCompanyOrderHistoryGraphByCategory($day, $category = InstapPlan::ALL_CATEGORY){
        date_default_timezone_set('Asia/Singapore');
        if(isset($_GET['category'])) { 
            if(!in_array($_GET['category'], array_keys(InstapPlan::allPlanCategory()))) {
               throw new CustomHttpException(Utility::jsonifyError("category", Yii::t("common", "Invalid category.")), CustomHttpException::BAD_REQUEST);
            }
        }else {
            throw new CustomHttpException(Utility::jsonifyError("category", Yii::t("common", "category cannot be empty.")), CustomHttpException::BAD_REQUEST);
        }
        $dealer = DealerUser::getDealerFromUserId(Yii::$app->user);
        $a = array();
        //toDo: need to change the time zone base on country
        for($i=0;$i<$day;$i++){
            if ($i == 0) { 
                $start = strtotime("today midnight 00:00:00");
                $end = strtotime("today midnight 23:59:59");
            } else {
                $start = strtotime("today -".$i."day midnight 00:00:00");
                $end = strtotime("today -".$i."day midnight 23:59:59"); 
            }
            $dealerOrder = DealerOrder::find()->byCategory($category)->andWhere(['dealer_order.dealer_company_id'=>$dealer->id])->andWhere(['>=', 'dealer_order.created_at', $start])->andWhere(['<=', 'dealer_order.created_at', $end])->count();
            $o = (object) []; 
            $d = date("d/m", $start);
            $o->$d = $dealerOrder;
            array_push($a,$o);
        }
        $a = array_reverse($a);
        Yii::$app->api->sendSuccessResponse($a);
    }
    //this function need to remain to support app before reskinning
    //toDo: delete this after we force all user to update app
    public function actionOrderHistoryGraph($day, $dealer_user_id = null){
        date_default_timezone_set("Asia/Singapore"); 
        $dealer = DealerUser::getDealerFromUserId(Yii::$app->user);
        $a = array();

        for($i=0;$i<$day;$i++){
            if ($i == 0) { 
                $start = strtotime("today midnight 00:00:00");
                $end = strtotime("today midnight 23:59:59");
            } else {
                $start = strtotime("today -".$i."day midnight 00:00:00");
                $end = strtotime("today -".$i."day midnight 23:59:59"); 
            }
            $dealerOrder = DealerOrder::find()->andWhere(['dealer_company_id'=>$dealer->id]);
            if($dealer_user_id){
                $dealerOrder = $dealerOrder->andWhere(['dealer_user_id' => $dealer_user_id]);
            } else {
                $dealerOrder = $dealerOrder->andWhere(['dealer_user_id' => Yii::$app->user->identity->id]);
            }
            $dealerOrder = $dealerOrder->andWhere(['>=', 'created_at', $start])->andWhere(['<=', 'created_at', $end])->count();
            $o = (object) [];
            $d = date("d/m", $start);
            // $d = date("D d/m", $start);
            $o->$d = $dealerOrder;
            array_push($a,$o);
        }
        $a = array_reverse($a);
        Yii::$app->api->sendSuccessResponse($a);
    }

    //use for app after reskinning 
    public function actionOrderHistoryGraphByCategory($day, $dealer_user_id = null, $category = InstapPlan::ALL_CATEGORY){
        date_default_timezone_set("Asia/Singapore"); 
        if(isset($_GET['category'])) { 
            if(!in_array($_GET['category'], array_keys(InstapPlan::allPlanCategory()))) {
               throw new CustomHttpException(Utility::jsonifyError("category", Yii::t("common", "Invalid category.")), CustomHttpException::BAD_REQUEST);
            }
        }else {
            throw new CustomHttpException(Utility::jsonifyError("category", Yii::t("common", "category cannot be empty.")), CustomHttpException::BAD_REQUEST);
        }
        $dealer = DealerUser::getDealerFromUserId(Yii::$app->user);
        $a = array();

        for($i=0;$i<$day;$i++){
            if ($i == 0) { 
                $start = strtotime("today midnight 00:00:00");
                $end = strtotime("today midnight 23:59:59");
            } else {
                $start = strtotime("today -".$i."day midnight 00:00:00");
                $end = strtotime("today -".$i."day midnight 23:59:59"); 
            }
            $dealerOrder = DealerOrder::find()->byCategory($category)->andWhere(['dealer_order.dealer_company_id'=>$dealer->id]);
            if($dealer_user_id){
                $dealerOrder = $dealerOrder->andWhere(['dealer_order.dealer_user_id' => $dealer_user_id]);
            } else {
                $dealerOrder = $dealerOrder->andWhere(['dealer_order.dealer_user_id' => Yii::$app->user->identity->id]);
            }
            $dealerOrder = $dealerOrder->andWhere(['>=', 'dealer_order.created_at', $start])->andWhere(['<=', 'dealer_order.created_at', $end])->count();
            $o = (object) [];
            $d = date("d/m", $start);
            // $d = date("D d/m", $start);
            $o->$d = $dealerOrder;
            array_push($a,$o);
        }
        $a = array_reverse($a);
        Yii::$app->api->sendSuccessResponse($a);
    }

    public function actionRegisterPlanPhoto() {
        if (!isset($_POST['plan_pool_id'])) {
            throw new CustomHttpException(Utility::jsonifyError("plan_pool_id", Yii::t("common","No plan pool id.")), CustomHttpException::BAD_REQUEST);
        }
        $form = new RegisterPlanForm();
        $form->scenario = RegisterPlanForm::SCENARIO_PHOTO;
        $form->plan_pool_id = $_POST["plan_pool_id"];
        $form->image_file = UploadedFile::getInstancesByName("image_file");

        if ($form->validate() && $pool = $form->registerPhoto()) {
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

    public function actionStaffMovement($page = 0, $pageSize = self::MAX_ROW_PER_PAGE) {
        $limit = ($pageSize > self::MAX_ROW_PER_PAGE) ? self::MAX_ROW_PER_PAGE : $pageSize; //page size
        $offset = $page * $limit;
        $dealer = DealerUser::getDealerFromUserId(Yii::$app->user);
        if($dealer){
            $staff_movement = DealerUserHistory::find()->where(['dealer_company_id' => $dealer->id])->limit($limit)->offset($offset)->orderBy(['created_at'=>SORT_DESC])->all();
            $sm = DealerUserHistory::toObjectArray($staff_movement);
            Yii::$app->api->sendSuccessResponse($sm);
        }else{
            $errMsg = Yii::t("common", "Staff does not exist."); 
            throw CustomHttpException::internalServerError($errMsg);
        }
    }

    public function actionUpdateProfile(){
        $form = new UpdateCompanyProfileForm();
        $hasDetail = isset($_POST['detail']);
        $hasPhoto = count($_FILES) > 0;
        $scenario=null;
        if (!$hasDetail && !$hasPhoto) {
            throw new CustomHttpException(Utility::jsonifyError("detail", "No detail or image_file."), CustomHttpException::BAD_REQUEST);
        }
        if ($hasPhoto) {
            $form->scenario = UpdateCompanyProfileForm::SCENARIO_PHOTO;
            $form->image_file = UploadedFile::getInstancesByName("image_file");
        }

        if ($form->validate() && $form->update()) {
            $company =DealerUser::getDealerFromUserId(Yii::$app->user);
            $d = $company->toObject();
            Yii::$app->api->sendSuccessResponse($d);
        }
        throw CustomHttpException::validationError($form);
    }

    public function actionViewInfo() {
        if (!isset($this->request["dealer_company_ids"])) {
            $str =  Utility::jsonifyError("dealer_company_ids", "Missing dealer company ids.");
            throw new CustomHttpException($str, CustomHttpException::UNPROCESSABLE_ENTITY);
        }
        $ids = $this->request['dealer_company_ids']; //for Body->raw in postman
        $only_integer =  $ids == array_filter($ids, 'is_numeric');
        if (!$only_integer) {
            $str =  Utility::jsonifyError("dealer_company_ids", "Incorrect dealer company ids.");
            throw new CustomHttpException($str, CustomHttpException::UNPROCESSABLE_ENTITY);
        }
        $user = Yii::$app->user;
        $dealer_company = DealerUser::getDealerFromUserId($user);

        $flag = DealerCompany::isSameLinearOrganisation($dealer_company->id, $ids);
        if ($flag) {
            $data = [];
            // $data["dealerCompany"] = "ok";
        //get company downline info 
            foreach($ids as $id) {
                $m = DealerCompany::find()->Where(['id'=>$id])->one();
                $d = $m->toObject(); 
                array_push($data, $d);
            }      
            //ToDO:: LIsT ALL INFO     
            Yii::$app->api->sendSuccessResponse($data);
        } else {
            $str =  Utility::jsonifyError("dealer_company_ids", Yii::t("common","Not authorized company ids."));
            throw new CustomHttpException($str, CustomHttpException::UNAUTHORIZED);
        }

    }

    public function actionPlanDetail() {
        $data = [];
        $company = DealerUser::getDealerFromUserId(Yii::$app->user);
        $dealer_company_id = $company->id;
        $plan_pool_id_arr = $this->request["plan_pool_ids"];  
        $pool_id_arr = DealerOrder::getMatchedPlanPoolIdByCompany($plan_pool_id_arr, $dealer_company_id);
        // print_r($pool_id_arr);
        // exit();

        if(!empty($pool_id_arr)) {
            $plans = DealerOrder::find()->where(['in', 'plan_pool_id', $pool_id_arr])->all();
            foreach ($plans as $plan) {
                if($plan) {
                    array_push($data, $plan->userPlan->allPlanDetailObject());
                }
            }
        } else {
            $str =  Utility::jsonifyError("plan_pool_id", Yii::t("common","Not authorized to get plan detail for this company."));
            throw new CustomHttpException($str, CustomHttpException::UNAUTHORIZED);
        }
        Yii::$app->api->sendSuccessResponse($data);

    }
    //NOTE: NOT ABLE TO VIEW COMPANY SIBLING 
    public function actionOrganizationChart() {
        $family = [];
        $user = Yii::$app->user;
        $dealer_company = DealerUser::getDealerFromUserId($user);
        $my_company_id = $dealer_company->id;

        $array_company = DealerCompanyDealer::find()->asArray()->all();
        $all_company = DealerCompany::mapCompanyNameToArray($array_company);

        $grandParent = DealerCompany::findUplinePath($my_company_id, $all_company);
        $grandChildren = DealerCompany::grandChildren($all_company, $my_company_id);
        $all = array_merge($grandChildren, $grandParent);

        $topmost_line = DealerCompany::findTopmostCompany($my_company_id, $all);
        $branch = DealerCompany::buildTree($all, $topmost_line);

        $family['id'] = $topmost_line;
        $family['name'] = DealerCompany::find()->where(['id' => $topmost_line])->one()->business_name;
        $family['children'] = $branch;

        Yii::$app->api->sendSuccessResponse($family);
    }

    //note: see all the hierarchy of company organisation
    // public function actionOrganizationChart1() {
    //     $user = Yii::$app->user;
    //     $dealer_company = DealerUser::getDealerFromUserId($user);

    //     $family = [];

    //     $all_company = DealerCompanyDealer::find()->asArray()->all();

    //     $array_company = DealerCompany::mapCompanyNameToArray($all_company); 
    //     // $array_company = json_decode(json_encode($arr_map), true); //chg stdObject to array

    //     $topmost_line = DealerCompany::findTopmostCompany($dealer_company->id, $all_company);
    //     $branch = DealerCompany::buildTree($array_company, $topmost_line);

    //     $family['id'] = $topmost_line;
    //     $family['name'] = DealerCompany::find()->where(['id' => $topmost_line])->one()->business_name;
    //     $family['children'] = $branch;

    //     Yii::$app->api->sendSuccessResponse($family);

    // }


}