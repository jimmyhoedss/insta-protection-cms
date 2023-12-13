<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use common\components\Utility;
use common\models\DealerCompany;
use common\models\InstapPlan;
use common\models\UserPlanAction;
use common\models\UserPlan;
use common\models\UserCase;
use common\models\UserProfile;
use common\models\DealerOrder;
use common\components\MyCustomActiveRecord;

use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\helpers\Url;
use common\behaviors\MyAuditTrailBehavior;

class InstapPlanPool extends MyCustomActiveRecord
{
    const STATUS_PENDING_REGISTRATION = "pending_registration";
    const STATUS_PENDING_APPROVAL = "pending_approval";
    const STATUS_REQUIRE_CLARIFICATION = "require_clarification";
    const STATUS_ACTIVE = "active";
    const STATUS_PENDING_CLAIM = "pending_claim";
    const STATUS_COMPLETE_CLAIM = "complete_claim";
    const STATUS_EXPIRED = "expired";
    const STATUS_CANCEL = "cancel";
    const STATUS_REJECT = "reject";

    const STATUS_TYPE_ALL = "all"; 
    const STATUS_TYPE_ACTIVE = "active"; 
    const STATUS_TYPE_INACTIVE = "inactive"; 

    public $plan_status_subtype;

    public static function tableName() {
        return 'instap_plan_pool';
    }

    public function init() {
        //default
        // $this->plan_status = self::STATUS_PENDING_REGISTRATION; //this will affect the filter selection
        //trigger after a plan is generated.
        $this->on(ActiveRecord::EVENT_AFTER_INSERT, [$this, 'formUpPolicyNumber']);
    }

    public function rules() {
        return [
            [['plan_id', 'dealer_company_id', 'user_id', 'region_id', 'plan_category', 'plan_sku', 'policy_number'], 'required'],
            [['plan_id', 'dealer_company_id', 'user_id', 'coverage_start_at', 'coverage_end_at', 'ew_coverage_start_at', 'ew_coverage_end_at'], 'integer'],
            [['plan_category', 'plan_status', 'notes', 'status', 'plan_status_subtype'], 'string'],
            [['region_id'], 'string', 'max' => 8],
            [['plan_sku'], 'string', 'max' => 64],
            [['policy_number'], 'string', 'max' => 255],
            ['plan_status', 'default', 'value' => self::STATUS_PENDING_REGISTRATION],
            ['plan_status', 'in', 'range' => array_keys(self::allPlanStatus())]            
        ];
    }

    public function behaviors() {
        return [
            'timestamp'  => [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at', 'coverage_start_at', 'ew_coverage_start_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at',],
                ],
            ],
            "blame" => BlameableBehavior::className(),
            "auditTrail" => MyAuditTrailBehavior::className(),
        ];
    }

    public function attributeLabels() {
        return [
            'id' => Yii::t('common', 'ID'),
            'plan_id' => Yii::t('common', 'Plan ID'),
            'dealer_company_id' => Yii::t('common', 'Dealer Company ID'),
            'user_id' => Yii::t('common', 'User ID'),
            'region_id' => Yii::t('common', 'Region ID'),
            'plan_category' => Yii::t('common', 'Plan Category'),
            'plan_sku' => Yii::t('common', 'Plan SKU'),
            'policy_number' => Yii::t('common', 'Policy Number'),
            'plan_status' => Yii::t('common', 'Plan Status'),
            'notes' => Yii::t('common', 'Notes'),
            'coverage_start_at' => Yii::t('common', 'Coverage Start At'),
            'coverage_end_at' => Yii::t('common', 'Coverage End At'),
            'ew_coverage_start_at' => Yii::t('common', 'E/W Coverage Start At'),
            'ew_coverage_end_at' => Yii::t('common', 'E/W Coverage End At'),
            'status' => Yii::t('common', 'Status'),
            'created_at' => Yii::t('common', 'Created At'),
            'created_by' => Yii::t('common', 'Created By'),
            'updated_at' => Yii::t('common', 'Updated At'),
            'updated_by' => Yii::t('common', 'Updated By'),
        ];
    }

    public static function allPlanStatus(){
        return [
            self::STATUS_PENDING_REGISTRATION =>Yii::t('common', "Pending Registration"),
            self::STATUS_PENDING_APPROVAL => Yii::t('common', 'Pending Approval'),
            self::STATUS_REQUIRE_CLARIFICATION => Yii::t('common', 'Require Clarification'),
            self::STATUS_ACTIVE => Yii::t('common', 'Active'),
            self::STATUS_PENDING_CLAIM => Yii::t('common', 'Pending Claim'),
            self::STATUS_COMPLETE_CLAIM => Yii::t('common', 'Complete Claim'),            
            self::STATUS_EXPIRED => Yii::t('common', 'Expired'),
            self::STATUS_CANCEL => Yii::t('common', 'Cancel'),
            self::STATUS_REJECT => Yii::t('common', 'Reject'),
        ];
    }
    //oh: status allow to submit imei and serial number in duplicate
    public static function planStatusAllowForRegister(){
        return [
            self::STATUS_PENDING_REGISTRATION, //for partial submit
            self::STATUS_COMPLETE_CLAIM,            
            self::STATUS_EXPIRED,
            self::STATUS_CANCEL,
            self::STATUS_REJECT,
        ];
    }
    //action will update to the corresponding plan status
    public static function mapActionToStatus() {
        return [
            UserPlanAction::ACTION_REGISTRATION => self::STATUS_PENDING_APPROVAL,
            UserPlanAction::ACTION_PHYSICAL_ASSESSMENT => self::STATUS_PENDING_REGISTRATION,
            UserPlanAction::ACTION_UPLOAD_PHOTO => self::STATUS_PENDING_REGISTRATION,
            UserPlanAction::ACTION_REQUIRE_CLARIFICATION => self::STATUS_REQUIRE_CLARIFICATION,
            UserPlanAction::ACTION_REGISTRATION_RESUBMIT => self::STATUS_PENDING_APPROVAL,
            UserPlanAction::ACTION_APPROVE => self::STATUS_ACTIVE,
            UserPlanAction::ACTION_CANCEL => self::STATUS_CANCEL,
            UserPlanAction::ACTION_REJECT => self::STATUS_REJECT,
        ];
    }    

    public static function planStatusType($status_type ){
        $arr = [];
        if ($status_type == self::STATUS_TYPE_ACTIVE) {
           $arr = [
                self::STATUS_PENDING_REGISTRATION,
                self::STATUS_PENDING_APPROVAL,
                self::STATUS_REQUIRE_CLARIFICATION,
                self::STATUS_PENDING_CLAIM,
                self::STATUS_ACTIVE 
            ]; 
        } else if ($status_type == self::STATUS_TYPE_INACTIVE) {
            $arr = [
                self::STATUS_COMPLETE_CLAIM ,
                self::STATUS_EXPIRED,
                self::STATUS_CANCEL,
                self::STATUS_REJECT
            ];
        } else {
            //all
            $arr = array_keys(self::allPlanStatus());
        }
        return $arr;
    }

    public function getPlan() {
        return $this->hasOne(InstapPlan::className(), ['id' => 'plan_id']);
    }
    public function getUser() {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
    public function getDealer() {
        return $this->hasOne(DealerCompany::className(), ['id' => 'dealer_company_id']);
    }
    public function getUserProfile() {
        return $this->hasOne(UserProfile::className(), ['user_id' => 'user_id']);
    }
    public function getUserPlan() {
        return $this->hasOne(UserPlan::className(), ['plan_pool_id' => 'id']);
    }
    public function getUserPlanAction() {
        return $this->hasMany(UserPlanAction::className(), ['plan_pool_id' => 'id']);
    }
    public function getDealerOrder() {
        return $this->hasOne(DealerOrder::className(), ['plan_pool_id' => 'id']);
    }
    public function getUserCase() {
        return $this->hasOne(UserCase::className(), ['plan_pool_id' => 'id'])->where(['in', 'current_case_status', UserCase::statusNotReject()])->orderBy(['created_at'=>SORT_DESC]);
    }
    // public function getProfile() {
    //     return $this->hasOne(UserProfile::className(), ['instap_plan_pool.user_id' => 'user_id']);
    // }
    //trigger by event EVENT_AFTER_INSERT, need to be public function
    public function formUpPolicyNumber() {
        //form up policy number
        $padDealerId = str_pad((string)$this->dealer_company_id, 3, "0", STR_PAD_LEFT);
        $padPoolId = str_pad((string)$this->id, 8, "0", STR_PAD_LEFT);
        // $year = "19";
        $year = date("y", strtotime("today"));
        $str = $this->plan_sku . "-" . $year . "-" . $padDealerId . "-" . $padPoolId;
        //$m->policy_number = "SGIP-SP-CP01-19-001-00000001";
        $this->updateAttributes(['policy_number' => $str]);
    }

    public function toApiResponseFormat() {
        $m = $this;
        $o = (object) [];
        $o->id = $m->id;
        $o->policy_number = $m->policy_number;
        return $o;
    }

    public static function makeModel($plan, $dealer, $user) {
        if ($plan == null || $dealer == null || $user == null) {
            return null;
        }
        //print_r($user->attributes);
        //exit();
        $m = new InstapPlanPool();
        $m->plan_id = $plan->id;
        $m->dealer_company_id = $dealer->id;
        $m->user_id = $user->id;
        $m->plan_category = $plan->category;
        $m->region_id = $plan->region_id;
        $m->plan_sku = $plan->sku;
        $m->policy_number = "-";
        // date_default_timezone_set("Asia/Singapore");
        $m->coverage_end_at = strtotime("+".$plan->coverage_period." months midnight -1 day");
        $m->ew_coverage_end_at = strtotime("+".$plan->ew_coverage_period." months midnight -1 day");
        return $m;
    }

    public function checkRegtrationChecklistComplete(){
        $hasRegistration = UserPlanAction::hasAction($this, UserPlanAction::ACTION_REGISTRATION);
        $hasUploadPhoto = UserPlanAction::hasAction($this, UserPlanAction::ACTION_UPLOAD_PHOTO);
        $hasPhysicalAssessment = UserPlanAction::hasAction($this, UserPlanAction::ACTION_PHYSICAL_ASSESSMENT);

        if($hasRegistration && $hasUploadPhoto && $hasPhysicalAssessment){
            return true;
        }
        return false;
    }

    public function getDealerCompany() {
        return $this->hasOne(DealerCompany::class, ['id' => 'dealer_company_id']);
    }

    public static function countTotalPendingApproval()
    {
        return self::find()->andWhere(['plan_status' => self::STATUS_PENDING_APPROVAL, 'region_id' => Yii::$app->session->get('region_id')])->count();
    }

    //*********** html layout ***********
    public function getPolicyDetailLayout() {
        $model = $this;
        $linkToCompany = Url::to(['dealer-company/view', 'id' =>$model->dealerCompany->id]);
        $linkToStaff = Url::to(['dealer-user/view', 'id' => $model->dealerOrder->dealer_user_id]);

        $html = "<table class='table'><thead><tr>";
        $html .= "<th width='150'>".Yii::t("common", "Plan Name")."</th>";
        $html .= "<th width='*'>".Yii::t("common","Policy Number")."</th>";
        $html .= "<th width='150'>".Yii::t("common","Coverage Period")."</th>";

        $html .= "<th width='200'>".Yii::t("common","Purchased From")."</th>";
        $html .= "<th width='100'>".Yii::t("common","Plan Status")."</th>";
        //$html .= "<th>Created At</th>";
        $html .= "</tr></thead>";
        $html .= "<tbody><tr>";
        $html .= "<td>" . $model->plan->getPlanBanner() . "</td>";
        $html .= "<td>" . $model->policy_number . "</td>";
        $html .= "<td>" . $model->getCoverageLayout() . "</td>";
        $html .= "<td>" . $model->dealerCompany->getContactSmallLayout($linkToCompany). 
                 "<br>" .$model->dealerOrder->userProfile->getAvatarSmallLayout($linkToStaff).
                 "</td>";
        // $html .= "<td>" . $model->dealerCompany->getContactLayout() . "</td>";
        $html .= "<td>" . $model->getStatusLayout() . "</td>";
        //$html .= "<td>" . Yii::$app->formatter->asDatetime($model->created_at) . "</td>";
        //$html .= "<td>" . MyCustomActiveRecord::getStatusHtml($model) . "</td>";
        $html .= "</tr></tbody></table>";

        return $html;
    }
    public function getCoverageLayout() {
        $model = $this;
        $future = $model->coverage_end_at; //Future date.
        $timefromdb = strtotime('today'); //source time
        $timeleft = $future-$timefromdb;
        $daysleft = round((($timeleft/24)/60)/60); 
        if($daysleft > 0){
            $html = "<p>".Yii::t('common', 'start:')." <b>".Yii::$app->formatter->asDate($model->coverage_start_at)."</b><br>";
            $html .= Yii::t('common', 'end:')."&nbsp; <b>".Yii::$app->formatter->asDate($model->coverage_end_at)."</b><br>";
            $html .= "[".$daysleft." ".Yii::t('common','days remaining.')."]</p>";
        } else {
            $html = "<p>" .Yii::t('common','Expired'). "</p>";
        }
        // $html .= "[".$model->plan->coverage_period." months]</p>";
        return $html;
    }
    public function getStatusLayout() {
        $model = $this;
        $status = InstapPlanPool::allPlanStatus()[$model->plan_status];
        if ($model->plan_status == InstapPlanPool::STATUS_PENDING_CLAIM) {
            $case = UserCase::find()->where(['in', 'current_case_status', UserCase::statusNotReject()])->andWhere(['plan_pool_id'=>$model->id])->orderBy(['created_at'=>SORT_DESC])->limit(1)->one();
            $status = UserCase::allCaseStatus()[$case->current_case_status];
        } 

        return $status;


        //Loynote:: what is this?
        /*
        return $model->plan_status == InstapPlanPool::STATUS_PENDING_CLAIM ? ucwords(str_replace('_', ' ', UserCase::find()->andWhere(['plan_pool_id'=>$model->id])->orderBy(['created_at'=>SORT_DESC])->one()->current_case_status)) : ucwords(str_replace('_', ' ', $model->plan_status));ucwords(str_replace('_', ' ', $model->plan_status));

        */
    }

    
}

