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
use common\matchcallback\DealerMatchCallBack;

use common\components\Utility;

use common\models\fcm\FcmStockRequest;
use common\models\fcm\FcmAllocateStock;
use common\models\form\CompanyInventoryForm;


use api\behaviours\ApiAuth;
use api\behaviours\VerbCheck;
use api\components\HttpBearerAuth;
use api\components\CustomHttpException;


class CompanyInventoryController extends RestControllerBase
{
    const MAX_ROW_PER_PAGE = 20;
    public $layout = false;

    public function behaviors() {
        return array_merge(parent::behaviors(), [
            
            'verbs' => [
                'class' => \yii\filters\VerbFilter::className(),
                'actions' => [
                    'index' => ['GET'],                    
                    'request-stock' => ['GET'],
                    'allocate-stock' => ['POST'],
                    'activate-stock' => ['POST'],
                    'view-inventory' => ['POST'],
                    'view-inventory-by-category' => ['POST'],
                    'allocation-history' => ['GET']
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
                       'actions' => ['allocation-history'],
                       'allow' => true,
                       'roles' => [User::ROLE_DEALER_ASSOCIATE],
                    ],
                    
                    [  'actions' => ['request-stock','allocation-history'],
                       'allow' => true,
                       'roles' => [User::ROLE_DEALER_MANAGER],
                    ], 

                    [
                       'actions' => ['allocate-stock'],
                       'allow' => true,
                       'matchCallback' => function ($rule, $action){
                            $dealer_user_id = Yii::$app->user->id;
                            $du = DealerUser::find()->where(['user_id' => $dealer_user_id])->active()->one();
                            if(isset($du)) {
                                $company_id = $du->dealer_company_id;
                                $match = DealerCompany::find()->andWhere(['id'=> $company_id])->andWhere(['sp_inventory_allocation_mode' => DealerCompany::ALLOCATION_MODE_ALLOCATE])->one() && Yii::$app->user->can(User::ROLE_DEALER_MANAGER);
                            } else {
                                $match = false;
                            }
                            return $match;
                       }
                    ],

                    [
                       'actions' => ['activate-stock'],
                       'allow' => true,
                       'matchCallback' => function ($rule, $action){
                            $dealer_user_id = Yii::$app->user->id;
                            $du = DealerUser::find()->where(['user_id' => $dealer_user_id])->active()->one();
                            if(isset($du)) {
                                $company_id = $du->dealer_company_id;
                                $match = DealerCompany::find()->andWhere(['id'=> $company_id])->andWhere(['sp_inventory_allocation_mode' => DealerCompany::ALLOCATION_MODE_ACTIVATE])->one() && Yii::$app->user->can(User::ROLE_DEALER_MANAGER);
                            } else {
                                $match = false;
                            }

                            return $match;
                       }
                    ],
                    [
                       'actions' => ['activate-stock', 'allocate-stock'],
                       'allow' => true,
                       'matchCallback' => function ($rule, $action){
                            $dealer_user_id = Yii::$app->user->id;
                            $du = DealerUser::find()->where(['user_id' => $dealer_user_id])->active()->one();
                            if(isset($du)) {
                                $company_id = $du->dealer_company_id;
                                $match = DealerCompany::find()->andWhere(['id'=> $company_id])->andWhere(['sp_inventory_allocation_mode' => DealerCompany::ALLOCATION_MODE_ALLOCATE_OR_ACTIVATE])->one() && Yii::$app->user->can(User::ROLE_DEALER_MANAGER);
                            } else {
                                $match = false;
                            }
                            return $match;
                       }
                    ],
                    [
                       'actions' => ['view-inventory', 'view-inventory-by-category'],
                       'allow' => true,
                       'matchCallback' => function ($rule, $action){
                            $dealer_user_id = Yii::$app->user->id;
                            $du = DealerUser::find()->where(['user_id' => $dealer_user_id])->active()->one();
                            $allow_role = Yii::$app->user->can(User::ROLE_DEALER_MANAGER) || Yii::$app->user->can(User::ROLE_DEALER_ASSOCIATE);
                            if(isset($du)) {
                                $company_id = $du->dealer_company_id;
                                $match = DealerCompany::find()->andWhere(['id'=> $company_id])->andWhere(['sp_inventory_order_mode' => DealerCompany::INVENTORY_MODE_STOCKPILE])->one() && $allow_role;
                            } else {
                                $match = false;
                            }
                            return $match;
                       }
                    ],

                ],
            ],
        ]);       
    }

    public function actionIndex()  {
        $o = (object) array("app"=>Yii::$app->name, "version"=>Yii::$app->params["apiVersion"], "endpoint"=>"company-inventory");
        Yii::$app->api->sendSuccessResponse($o);
    }

    
    public function actionRequestStock($plan_id, $amount) {

        $form = new CompanyInventoryForm();
        $form->scenario = CompanyInventoryForm::REQUEST_OR_ACTIVATE_STOCK;
        $form->plan_id = $plan_id;
        $form->amount = $amount;
        //sent fcm to all the dealer manager 
        if ($form->validate() && $form->sendStockRequest() != null) {
             $data = [];
             $data['message'] = Yii::t("common", 'Successfully requested stocks');
            Yii::$app->api->sendSuccessResponse($data);
        }else {
            throw CustomHttpException::validationError($form); 
        }
        
    }

    public function actionAllocateStock() {
        $form = new CompanyInventoryForm();
        $form->scenario = CompanyInventoryForm::SCENARIO_API_ALLOCATE;
        $form->attributes = $this->request;
        //sent fcm to all the dealer manager 
        if ($form->validate() && $stock = $form->allocateStock()) {
            $data = [];
            $data['message'] = Yii::t("common", 'Successfully allocated')." ".$this->request['amount'].' '.$stock->plan->name;
            Yii::$app->api->sendSuccessResponse($data);
        }else {
            throw CustomHttpException::validationError($form); 
        }

    }

    public function actionActivateStock() {
        $form = new CompanyInventoryForm();
        $form->attributes = $this->request;
        $form->scenario = CompanyInventoryForm::REQUEST_OR_ACTIVATE_STOCK;

        if ($form->validate() && $stock = $form->activateStock()) {
            $data = [];
            $data['message'] = Yii::t("common", 'Successfully activated')." ".$form->amount.' '.$stock->plan->name;
            Yii::$app->api->sendSuccessResponse($data);
        }else {
            throw CustomHttpException::validationError($form); 
        }
    }

    public function actionAllocationHistory($page = 0, $pageSize = self::MAX_ROW_PER_PAGE) {
        $limit = ($pageSize > self::MAX_ROW_PER_PAGE) ? self::MAX_ROW_PER_PAGE : $pageSize; //page size
        $offset = $page * $limit;
        $user = Yii::$app->user;
        $dealer_company = DealerUser::getDealerFromUserId($user);
        if(!$dealer_company) {
            throw CustomHttpException::internalServerError("Unable to display history.");
        }
        $dealer_company_id = $dealer_company->id;
        $datas = DealerInventoryAllocationHistory::find()->orWhere(['from_company_id'=> $dealer_company_id])->orWhere(['to_company_id'=> $dealer_company_id])->orderBy([
        'created_at' => SORT_DESC])->limit($limit)->offset($offset)->all();
        $a = [];
            foreach ($datas as $data) {
                array_push($a, $data->toObject());
            }
        Yii::$app->api->sendSuccessResponse($a);
    }

    //view inventory by array of company id
    //api for app before re-skinning version
    public function actionViewInventory() {
        $ids = $this->request['dealer_company_ids'];
        $region_id = Yii::$app->user->identity->region_id;

        if (!isset($this->request["dealer_company_ids"])) {
                $str =  Utility::jsonifyError("dealer_company_ids", Yii::t("common", "Missing dealer company ids."));
                throw new CustomHttpException($str, CustomHttpException::UNPROCESSABLE_ENTITY);
            }

        $ids = $this->request['dealer_company_ids']; //for Body->raw in postman
        $only_integer = $ids == array_filter($ids, 'is_numeric');
        if (!$only_integer) {
            $str =  Utility::jsonifyError("dealer_company_ids", Yii::t("common", "Incorrect dealer company ids."));
            throw new CustomHttpException($str, CustomHttpException::UNPROCESSABLE_ENTITY);
        }

        $user = Yii::$app->user;
        $dealer_company = DealerUser::getDealerFromUserId($user);
        $flag = DealerCompany::isSameLinearOrganisation($dealer_company->id, $ids);
        if ($flag) {
            $data = [];
             //get company downline info 
            foreach($ids as $id) {
                $m = DealerCompany::find()->Where(['id'=>$id])->one();
                $d = $m->invInfo();
                array_push($data, $d);
            }      
            //ToDO:: LIsT ALL INFO     
            Yii::$app->api->sendSuccessResponse($data);
        } else {
            $str =  Utility::jsonifyError("dealer_company_ids", Yii::t("common","Not authorized company ids."));
            throw new CustomHttpException($str, CustomHttpException::UNAUTHORIZED);
        }
        

    }
    //api for app re-skinning version
     public function actionViewInventoryByCategory() {
        
        if (!isset($this->request['dealer_company_ids'])) {
            $str =  Utility::jsonifyError("dealer_company_ids", Yii::t("common", "Missing dealer company ids."));
            throw new CustomHttpException($str, CustomHttpException::UNPROCESSABLE_ENTITY);
        }
        if (!isset($this->request['category'])) {
            $str =  Utility::jsonifyError("category", Yii::t("common", "Missing plan category."));
            throw new CustomHttpException($str, CustomHttpException::UNPROCESSABLE_ENTITY);
        }
        if(!in_array($this->request['category'], array_keys(InstapPlan::allPlanCategory()))) {
            throw new CustomHttpException(Utility::jsonifyError("category", "Invalid category."), CustomHttpException::BAD_REQUEST);
        }
        
        $category = $this->request['category'];
        $ids = $this->request['dealer_company_ids'];
        $region_id = Yii::$app->user->identity->region_id;
        $only_integer = array_filter($ids, 'is_numeric');

        if (!$only_integer) {
            $str =  Utility::jsonifyError("dealer_company_ids", Yii::t("common", "Incorrect dealer company ids."));
            throw new CustomHttpException($str, CustomHttpException::UNPROCESSABLE_ENTITY);
        }

        $user = Yii::$app->user;
        $dealer_company = DealerUser::getDealerFromUserId($user);
        $flag = DealerCompany::isSameLinearOrganisation($dealer_company->id, $ids);
        if ($flag) {
            $data = [];
             //get company downline info 
            foreach($ids as $id) {
                $m = DealerCompany::find()->Where(['id'=>$id])->one();
                $d = $m->invInfoByCategory($category);
                array_push($data, $d);
            }      
            //ToDO:: LIsT ALL INFO     
            Yii::$app->api->sendSuccessResponse($data);
        } else {
            $str =  Utility::jsonifyError("dealer_company_ids", Yii::t("common","Not authorized company ids."));
            throw new CustomHttpException($str, CustomHttpException::UNAUTHORIZED);
        }
        

    }


}