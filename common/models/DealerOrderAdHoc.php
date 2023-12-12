<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use common\behaviors\MyAuditTrailBehavior;
use common\components\Utility;
use common\models\DealerCompany;
use common\models\InstapPlan;

class DealerOrderAdHoc extends \yii\db\ActiveRecord
{
    public static function tableName() {
        return 'dealer_order_ad_hoc';
    }

    public function rules() {
        return [
            [['plan_id', 'dealer_company_id', 'activation_token', 'expire_at'], 'required'],
            [['plan_id', 'dealer_company_id', 'dealer_user_id', 'expire_at'], 'integer'],
            [['status'], 'string'],
            [['activation_token'], 'string', 'max' => 255],
            [['activation_token'], 'unique'],
        ];
    }

    public function behaviors()
    {
        return [
            "timestamp" => TimestampBehavior::className(),
            "blame" => BlameableBehavior::className(),
            "auditTrail" => MyAuditTrailBehavior::className(),
        ];
    }

    public function attributeLabels() {
        return [
            'id' => Yii::t('common', 'ID'),
            'plan_id' => Yii::t('common', 'Plan ID'),
            'dealer_company_id' => Yii::t('common', 'Dealer Company ID'),
            'dealer_user_id' => Yii::t('common', 'Dealer User ID'),
            'activiation_token' => Yii::t('common', 'Activiation Token'),
            'expire_at' => Yii::t('common', 'Expire At'),
            'status' => Yii::t('common', 'Status'),
            'created_at' => Yii::t('common', 'Created At'),
            'created_by' => Yii::t('common', 'Created By'),
            'updated_at' => Yii::t('common', 'Updated At'),
            'updated_by' => Yii::t('common', 'Updated By'),
        ];
    }

    public function getPlan() {
        return $this->hasOne(InstapPlan::class, ['id' => 'plan_id']);
    }
    public function getDealerCompany() {
        return $this->hasOne(DealerCompany::class, ['id' => 'dealer_company_id']);
    }
    public static  function deleteAllOrder($user, $plan) {
        SELF::deleteAll(['AND', 'dealer_user_id = :user_id', 'plan_id = :plan_id'], [':user_id' => $user->id, ':plan_id' => $plan->id]);
    }
    //delete old and generate new token
    public static  function makeModel($user, $plan, $dealer) {
        $m = new SELF();
        $m->activation_token = Utility::randomToken(64);
        $m->plan_id = $plan->id;
        $m->dealer_company_id = $dealer->id;
        $m->dealer_user_id = $user->id;
        //valid for 1 day
        $m->expire_at = time() + (60*60*24);
        return $m;
    }


    public static function find() {
        return new \common\models\query\DealerOrderAdHocQuery(get_called_class());
    }
}
