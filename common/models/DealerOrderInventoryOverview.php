<?php

namespace common\models;

use Yii;
use common\components\MyCustomActiveRecord;
use common\components\Utility;
use common\models\inventory\InventoryManagement;



class DealerOrderInventoryOverview extends MyCustomActiveRecord
{
    const SCENARIO_CMS_ALLOCATE = "scenario_cms_allocate";
    const SCENARIO_CMS_REVERT = "scenario_cms_revert";

    //mode for revert stock
    const REVERT_IP_ALLOCATE = 'revert_ip_allocated_stock';
    const REVERT_DEALER_ALLOCATE = 'revert_dealer_allocated_stock';
    const REVERT_DEALER_ACTIVATE = 'revert_daeler_activated_stock';

    public $assign_amount, $mode_stock_revert;
    // public $amount;
    
    public static function tableName()
    {
        return 'dealer_order_inventory_overview';
    }

   
    public function rules()
    {
        return [
            // [['dealer_company_id', 'quota','amount','plan_id'], 'required', 'on' => self::SCENARIO_API_ALLOCATE],
            [['dealer_company_id','assign_amount','plan_id'], 'required', 'on' => self::SCENARIO_CMS_ALLOCATE],
            [['dealer_company_id','assign_amount','plan_id'], 'required', 'on' => self::SCENARIO_CMS_REVERT],
            ['assign_amount', 'compare', 'compareValue' => 1000, 'operator' => '<=', 'type' => 'number',  'message' => Yii::t('common','Revert amount cannot more than 1000.'), 'on' => self::SCENARIO_CMS_REVERT],
            [['dealer_company_id', 'plan_id', 'quota', 'overall', 'assign_amount'], 'integer'],
            [['status'], 'string'],
            [['mode_stock_revert'], 'safe'],
        ];
    }
    // public function behaviors()
    // {
    //     // array_merge(array1)
    // }
    // 'cacheInvalidate' => [
    //             'class' => CacheInvalidateBehavior::class,
    //             'cacheComponent' => 'frontendCache',
    //             'keys' => [
    //                 function ($model) {
    //                     return [
    //                         self::class,
    //                         $model->key
    //                     ];
    //                 }
    //             ]
    //         ]

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'dealer_company_id' => Yii::t('common', 'Dealer Company ID'),
            'plan_id' => Yii::t('common', 'Plan ID'),
            'quota' => Yii::t('common', 'Quota'),
            'overall' => Yii::t('common', 'Overall'),
            'status' => Yii::t('common', 'Status'),
            'created_at' => Yii::t('common', 'Created At'),
            'created_by' => Yii::t('common', 'Created By'),
            'updated_at' => Yii::t('common', 'Updated At'),
            'updated_by' => Yii::t('common', 'Updated By'),
        ];
    }

    static public function allRevertMode() {
        return [
            self::REVERT_IP_ALLOCATE => "REVERT IP ALLOCATE", 
            self::REVERT_DEALER_ALLOCATE => "REVERT DEALER ALLOCATE",
            self::REVERT_DEALER_ACTIVATE => "REVERT DAELER ACTIVATE"
        ];
    }

    public static function makeModel($downline_id, $amount, $plan_id) {
        $m = SELF::find()->andWhere(['dealer_company_id'=>$downline_id])->andWhere(['plan_id'=> $plan_id])->one();
        if (!$m) { $m = new SELF(); }
        $m->dealer_company_id = $downline_id;
        $m->plan_id = $plan_id;
        $m->quota = ($m->quota != null) ? $m->quota + $amount : $amount;
        $m->overall = ($m->overall != null) ?$m->overall + $amount :$amount;

        return $m;
    }


    public function getPlan() {
        return $this->hasOne(InstapPlan::className(), ['id' => 'plan_id']);
    }
    public function getDealer() {
        return $this->hasOne(DealerCompany::className(), ['id' => 'dealer_company_id']);
    }

    public function toObject() {
        $m = $this;

        $o = (object) [];
        // $o->dealer_company_id = $m->dealer_company_id;
        $o->plan_id = $m->plan_id;
        $o->plan_name = $m->plan->name;
        $o->category = $m->plan->category;
        $o->tier = $m->plan->tier;
        $o->plan_description = $m->plan->description;
        // $o->thumbnail_base_url = $m->plan->thumbnail_base_url;
        $o->thumbnail_presigned = $m->plan->thumbnail_path ? Utility::getPreSignedS3Url($m->plan->thumbnail_path) : "";
        $o->sku = $m->plan->sku;
        $o->free_stock = $m->quota;
        $o->activated_remaining_stock = SELF::getRemainingStock($m->plan_id, $m->dealer_company_id);
        $o->activated_total_stock = SELF::getActivatedStock($m->plan_id, $m->dealer_company_id);
        $o->downline_free_stock = SELF::countAllDownlineInventory($m->dealer_company_id, $m->plan_id);
        $o->downline_total_allocated_stock = SELF::countDownlineAllocatedStock($m->dealer_company_id, $m->plan_id);
        $o->overall = $m->overall;

        return $o;
    }

    //Oh: going to remove this function is after few month run smoothly in production
    public static function showEmpty2($plan) {
        $m = $plan;
        $o = (object) [];
        
        $o->plan_id = $m->plan->id;
        $o->plan_name = $m->plan->name;
        $o->category = $m->plan->category;
        $o->tier = $m->plan->tier;
        $o->plan_description = $m->plan->description;
        $o->thumbnail_presigned = $m->plan->thumbnail_path ? Utility::getPreSignedS3Url($m->plan->thumbnail_path) : "";
        $o->sku = $m->plan->sku;
        $o->free_stock = 0;
        $o->activated_remaining_stock = 0;
        $o->activated_total_stock = 0;
        $o->downline_free_stock = 0;
        $o->downline_total_allocated_stock = 0;
        $o->overall = 0;

        return $o;
    }

    //Oh: backup function
    public static function showEmpty($dealer_plan) {
        $m = $dealer_plan;
        $o = (object) [];
        
        $o->plan_id = $m['id'];
        $o->plan_name = $m['name'];
        $o->category = $m['category'];
        $o->tier = $m['tier'];
        $o->plan_description = $m['description'];
        $o->thumbnail_presigned = $m['thumbnail_path'] ? Utility::getPreSignedS3Url($m['thumbnail_path']) : "";
        $o->sku = $m['sku'];
        $o->free_stock = 0;
        $o->activated_remaining_stock = 0;
        $o->activated_total_stock = 0;
        $o->downline_free_stock = 0;
        $o->downline_total_allocated_stock = 0;
        $o->overall = 0;

        return $o;
    }

    public static function countAllDownlineInventory($company_id, $plan_id) {
        $total = 0;
        $downline_arr = DealerCompanyDealer::getDownlineArray($company_id);
        if($downline_arr) {
            foreach($downline_arr as $downline) {
                $inv = DealerOrderInventoryOverview::find()->Where(['dealer_company_id'=> $downline['dealer_company_downline_id']])->andWhere(['plan_id' => $plan_id])->one();
                if($inv) {
                    $total = $total + $inv->quota;
                }
            }
        }

        return $total;
    }

    public static function countDownlineAllocatedStock($company_id, $plan_id) {
        $total = 0;
        $downline_arr = DealerCompanyDealer::getDownlineArray($company_id);
        if($downline_arr) {
            foreach($downline_arr as $downline) {
                $inventories = DealerInventoryAllocationHistory::find()->Where(['from_company_id'=> $company_id])->andWhere(['plan_id' => $plan_id])->andWhere(['IN','action', [
                        DealerInventoryAllocationHistory::ACTION_ALLOCATE,
                        DealerInventoryAllocationHistory::ACTION_REVERT_ALLOCATE
                    ]])
                    ->andWhere(['to_company_id' => $downline['dealer_company_downline_id']])->all();
                if($inventories) {
                    foreach($inventories as $inventory) {
                        $total = $total + $inventory->amount;
                    }
                }
            }
        }

        return $total;
    }

    public static function getRemainingStock($plan_id, $dealer_company_id) {
        $quantity = DealerOrderInventory::find()->andWhere(['plan_id' => $plan_id])->andWhere(['dealer_company_id' => $dealer_company_id])->andWhere(['plan_pool_id' => null])->andWhere(['or', ['activation_token' => null], ['<','expire_at', time()]])->andWhere(['status' => MyCustomActiveRecord::STATUS_ENABLED])->count();
        return $quantity;
    }

    public static function getActivatedStock($plan_id, $dealer_company_id) {
        $quantity = DealerOrderInventory::find()->andWhere(['plan_id' => $plan_id])->andWhere(['dealer_company_id' => $dealer_company_id])->count();
        return $quantity;
    }

    public static function getCompanyWithLowInventory($amount) {
        $company = DealerCompany::find()->select(['id'])->where(['region_id' => Yii::$app->session->get('region_id')])->asArray()->all();
        $company_id_arr = array_column($company, 'id');
        $d = DealerOrderInventoryOverview::find()->distinct()->select(['dealer_company_id'])->andWhere(['<=','quota', $amount])->andWhere(['in', 'dealer_company_id', $company_id_arr])->asArray()->all();

        return $d;
    }

    public function getUplineInventory() {
        $upline = DealerCompanyDealer::getUpline($this->dealer_company_id);

        if(!$upline) return null;

        return self::find()->andWhere(['dealer_company_id' => $upline->dealer_company_upline_id])->andWhere(['plan_id' =>$this->plan_id])->one();

    }

    // public static function find()
    // {
    //     return new \common\models\query\DealerCompanyQuery(get_called_class());
    // }

}
