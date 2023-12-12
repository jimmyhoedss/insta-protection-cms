<?php

namespace common\models;

use Yii;
use common\components\MyCustomActiveRecord;

class InstapPlanDealerCompany extends MyCustomActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'instap_plan_dealer_company';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['dealer_company_id', 'plan_id'], 'required'],
            [['dealer_company_id', 'plan_id', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['status'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'dealer_company_id' => Yii::t('common', 'Dealer Company ID'),
            'plan_id' => Yii::t('common', 'Plan ID'),
            'status' => Yii::t('common', 'Status'),
            'created_at' => Yii::t('common', 'Created At'),
            'created_by' => Yii::t('common', 'Created By'),
            'updated_at' => Yii::t('common', 'Updated At'),
            'updated_by' => Yii::t('common', 'Updated By'),
        ];
    }

    public function getPlan() {
        return $this->hasOne(InstapPlan::className(), ['id' => 'plan_id']);
    }

    public static function makeModel($plan_id, $dealer_company_id) {
        $m = new SELF();
        $m->plan_id = $plan_id;
        $m->dealer_company_id = $dealer_company_id;
        return $m;
    }   
}
