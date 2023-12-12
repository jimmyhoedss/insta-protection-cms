<?php

namespace common\models;

use Yii;
use common\components\MyCustomActiveRecord;
use common\models\UserCaseRepairCentre;
use common\models\UserPlan;
use common\components\Utility;


class DealerOrder extends MyCustomActiveRecord
{
    public static function tableName() {
        return 'dealer_order';
    }

    public function rules() {
        return [
            [['dealer_company_id', 'plan_pool_id'], 'required'],
            [['dealer_company_id', 'dealer_user_id', 'plan_pool_id', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['price'], 'number'],
            [['notes', 'status'], 'string'],
            [['plan_pool_id'], 'unique'],
        ];
    }

    public function attributeLabels() {
        return [
            'id' => Yii::t('common', 'ID'),
            'dealer_company_id' => Yii::t('common', 'Dealer Company ID'),
            'dealer_user_id' => Yii::t('common', 'Dealer User ID'),
            'plan_pool_id' => Yii::t('common', 'Plan Pool ID'),
            'price' => Yii::t('common', 'Price'),
            'notes' => Yii::t('common', 'Notes'),
            'status' => Yii::t('common', 'Status'),
            'created_at' => Yii::t('common', 'Created At'),
            'created_by' => Yii::t('common', 'Created By'),
            'updated_at' => Yii::t('common', 'Updated At'),
            'updated_by' => Yii::t('common', 'Updated By'),
        ];
    }

    public function getPlanPool() {
        return $this->hasOne(InstapPlanPool::className(), ['id' => 'plan_pool_id']);
    }
    public function getDetails() {
        return $this->hasOne(UserPlanDetail::className(), ['plan_pool_id' => 'plan_pool_id']);
    }
    public function getDealer() {
        return $this->hasOne(DealerCompany::className(), ['id' => 'dealer_company_id']);
    }
    public function getDealerUser() {
        return $this->hasOne(User::className(), ['id' => 'dealer_user_id']);
    }
    public function getUserProfile() {
        return $this->hasOne(UserProfile::className(), ['user_id' => 'dealer_user_id']);
    }
    public function getUserPlan() {
        return $this->hasOne(UserPlan::className(), ['plan_pool_id' => 'plan_pool_id']);
    }
    public function getUserPlanAction() {
        return $this->hasMany(UserPlanAction::className(), ['plan_pool_id' => 'plan_pool_id']);
    }

    public function toObject() {
        $m = $this;
        $p = $m->planPool;
        $plan = $p->plan;
        $u = $p->user;
        $du = $m->dealerUser->userProfile;
        $o = (object) [];
        //InstapPlan
        $instap_plan = (object) [];
        $user_profile = (object) [];
        $dealer_profile = (object) [];

        //presign image url
        if(isset($plan->thumbnail_path)) {
            $path = Utility::replacePath($plan->thumbnail_path);
            $planPreSignImage = Utility::getPreSignedS3Url($plan->thumbnail_path);
        }

        $instap_plan->plan_pool_id = $p->id;
        $instap_plan->plan_pool_status = $p->plan_status; 
        $instap_plan->current_plan_action ="";
        $instap_plan->policy_number = $p->policy_number;
        $instap_plan->name = $plan->name;
        $instap_plan->tier = $plan->tier;
        $instap_plan->category = $plan->category;
        $instap_plan->description = $plan->description;
        $instap_plan->webview_url = $plan->policyPdf;
        // $instap_plan->webview_url = $plan->webview_url;
        // $instap_plan->thumbnail_base_url = $plan->thumbnail_base_url;
        $instap_plan->thumbnail_presigned = $planPreSignImage;
        $instap_plan->coverage_start_at = $p->coverage_start_at;
        $instap_plan->coverage_end_at = $p->coverage_end_at;
        $instap_plan->sold_at = $m->created_at;
        $o->instap_plan = $instap_plan;
        $cps = get_object_vars($m->userPlan->planActionObject($p));
        if(!empty($cps)) {
            $instap_plan->current_plan_action = $cps['current_plan_action'];
        }

      
        $user_profile->first_name = utf8_decode($u->userProfile->first_name);
        $user_profile->last_name = utf8_decode($u->userProfile->last_name);
        // $user_profile->avatar_base_url = $u->userProfile->avatar_base_url;
        $user_profile->email = $u->email;
        $user_profile->avatar_url = $u->userProfile->avatar;
        $o->user = $user_profile;

        $dealer_profile->first_name = $du->first_name;
        $dealer_profile->last_name = $du->last_name;;
        // $dealer_profile->avatar_base_url = $du->avatar_base_url;
        $dealer_profile->avatar_url = $du->avatar;
        $o->dealer_user = $dealer_profile;


        return $o;
    }

    public static function getMatchedPlanPoolIdByCompany($plan_pool_id_arr, $dealer_company_id) {
       $planSold = SELF::find()->where(['dealer_company_id' => $dealer_company_id])->all();
       $pool_ids = array_column($planSold, 'plan_pool_id');
       $result = array_intersect($pool_ids, $plan_pool_id_arr);
       if($result) {
            return $result;
       }

       return null;
    }

    public static function find() {
        return new \common\models\query\DealerOrderQuery(get_called_class());
    }
    //get order to book-keeping
    public static function makeModel($dealer, $dealer_user_id, $pool) {
        $m = new SELF();
        $m->dealer_company_id = $dealer->id;
        $m->dealer_user_id = $dealer_user_id;
        $m->plan_pool_id = $pool->id;
        $m->price = $pool->plan->retail_price;
        $m->order_mode = $dealer->sp_inventory_order_mode;
        return $m;
    }

    public static function totalSalesOfYear() {
        $data = [];
        date_default_timezone_set("Asia/Singapore");
        $months = ['January','February','March','April','May','June','July','August','September','October','November','December'];

        for($i=0; $i<count($months); $i++) {
            $start = strtotime( $months[$i].date("Y"));
            $end = strtotime( $months[$i].date("Y")."+1 month"."-1 second");
            $sales = DealerOrder::find()->innerJoinWith('planPool', true)->andWhere(['instap_plan_pool.region_id' => Yii::$app->session->get("region_id")])->andWhere(['between', 'dealer_order.created_at', $start, $end ])->count();
         
            array_push($data, $sales);
        }

        return $data;
    }

    public static function totalSalesOfYear2() {
        $data = [];
        date_default_timezone_set("Asia/Singapore");
        $months = ['January','February','March','April','May','June','July','August','September','October','November','December'];

        for($i=0; $i<count($months); $i++) {
            $start = strtotime( $months[$i].date("2020"));
            $end = strtotime( $months[$i].date("2020")."+1 month"."-1 second");
            $sales = DealerOrder::find()->innerJoinWith('planPool', true)->andWhere(['instap_plan_pool.region_id' => Yii::$app->session->get("region_id")])->andWhere(['between', 'dealer_order.created_at', $start, $end ])->count();
         
            array_push($data, $sales);
        }

        return $data;
    }


}
