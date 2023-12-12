<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use common\behaviors\MyAuditTrailBehavior;
use common\models\UserPlanDetail;
use common\models\InstapPlanPool;
use common\behaviors\EditHistoryBehavior;


class UserPlanDetailEdit extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_plan_detail_edit';
    }
    public function behaviors() {
        return [
            "timestamp" => TimestampBehavior::className(),
            "blame" => BlameableBehavior::className(),
            "editTrail" => EditHistoryBehavior::className(),
        ];
    }  

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['plan_pool_id','sp_brand', 'sp_model_number', 'sp_serial', 'sp_imei', 'sp_color', 'notes'], 'required'],
            [['plan_pool_id'], 'integer'],
            [['notes'], 'string'],
            [['sp_brand', 'sp_model_number', 'sp_serial', 'sp_imei', 'sp_color', 'sp_dealer_code', 'sp_country_of_purchase', 'sp_device_purchase_date', 'sp_device_purchase_price', 'sp_device_capacity'], 'string', 'max' => 256],
            [['sp_model_name'], 'string', 'max' => 255],
            ['sp_imei', 'string', 'max'=>17, 'min'=> 15, 'message'=>'incorrect imei length'],
            [['plan_pool_id'], 'unique'],
            // [['sp_serial'], 'unique'],
            // [['sp_imei'], 'unique'],
            ['sp_serial',function ($attribute, $params) {
                $s = strtoupper($this->sp_serial);
                $m = UserPlanDetail::find()->joinWith(['planPool'])->andWhere(['UPPER(user_plan_detail.sp_serial)' => $s])->andWhere(['not in','instap_plan_pool.plan_status', InstapPlanPool::planStatusAllowForRegister()])->andWhere(['not in', 'instap_plan_pool.id', $this->planPool->id])->count();
                if($m > 0) {
                    $this->addError($attribute, 'There is another plan registered with this serial number.');
                    return true;
                }
                return false;
            }],
            ['sp_imei',function ($attribute, $params) {
                $s = strtoupper($this->sp_imei);
                $m = UserPlanDetail::find()->joinWith(['planPool'])->andWhere(['UPPER(user_plan_detail.sp_imei)' => $s])->andWhere(['not in','instap_plan_pool.plan_status', InstapPlanPool::planStatusAllowForRegister()])->andWhere(['not in', 'instap_plan_pool.id', $this->planPool->id])->count();
                if($m > 0) {
                    $this->addError($attribute, 'There is another plan registered with this IMEI.');
                    return true;
                }
                return false;
            }],

        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('backend', 'ID'),
            'plan_pool_id' => Yii::t('backend', 'Plan Pool ID'),
            'sp_brand' => Yii::t('backend', 'Smart Phone Brand'),
            'sp_model_number' => Yii::t('backend', 'Smart Phone Model Number'),
            'sp_model_name' => Yii::t('backend', 'Smart Phone Model Name'),
            'sp_serial' => Yii::t('backend', 'Smart Phone Serial Number'),
            'sp_imei' => Yii::t('backend', 'Smart Phone IMEI Number'),
            'sp_color' => Yii::t('backend', 'Smart Phone Color'),
            'sp_dealer_code' => Yii::t('backend', 'Smart Phone Dealer Code'),
            'sp_country_of_purchase' => Yii::t('backend', 'Smart Phone Country Of Purchase'),
            'sp_device_purchase_date' => Yii::t('backend', 'Smart Phone Device Purchase Date'),
            'sp_device_purchase_price' => Yii::t('backend', 'Smart Phone Device Purchase Price'),
            'sp_device_capacity' => Yii::t('backend', 'Smart Phone Device Capacity'),
            'notes' => Yii::t('backend', 'Edit Reasons/Notes'),
            'status' => Yii::t('backend', 'Status'),
            'created_at' => Yii::t('backend', 'Created At'),
            'created_by' => Yii::t('backend', 'Created By'),
            'updated_at' => Yii::t('backend', 'Updated At'),
            'updated_by' => Yii::t('backend', 'Updated By'),
        ];
    }
    public static function makeModel($plan_detail, $notes) {
        $m = SELF::find()->where(["plan_pool_id"=>$plan_detail->plan_pool_id])->one();
        if (!$m) { $m = new SELF(); }
        $m->plan_pool_id = $plan_detail->plan_pool_id;
        $m->sp_brand = strtoupper($plan_detail->sp_brand);
        $m->sp_model_number = $plan_detail->sp_model_number;
        $m->sp_model_name = UserPlanDetail::getModelName($plan_detail->sp_model_number);
        $m->sp_serial = strtoupper($plan_detail->sp_serial);
        $m->sp_imei = strtoupper($plan_detail->sp_imei);
        $m->sp_color = $plan_detail->sp_color;
        $m->sp_dealer_code = $plan_detail->sp_dealer_code;
        $m->sp_country_of_purchase = $plan_detail->sp_country_of_purchase;
        $m->sp_device_purchase_date = $plan_detail->sp_device_purchase_date;
        $m->sp_device_purchase_price = $plan_detail->sp_device_purchase_price;
        $m->sp_device_capacity = $plan_detail->sp_device_capacity;
        $m->notes = $notes;
        return $m;
    }

    public function getPlanPool()
    {
        return $this->hasOne(InstapPlanPool::className(), ['id' => 'plan_pool_id']);
    }

    public static function countTotalPendingEditApproval()
    {
        return SELF::find()->joinWith('planPool', true)->andWhere(['instap_plan_pool.region_id' => Yii::$app->session->get('region_id')])->count();
    }


    public static function find() {
        return new \common\models\query\UserPlanDetailEditQuery(get_called_class());
    }
}
