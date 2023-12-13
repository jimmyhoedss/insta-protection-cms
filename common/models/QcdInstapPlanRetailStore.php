<?php

namespace common\models;

use Yii;
use common\models\QcdRetailStore;
use common\models\InstapPlan;
use common\components\MyCustomActiveRecord;

class QcdInstapPlanRetailStore extends MyCustomActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'qcd_instap_plan_retail_store';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['instap_plan_id', 'retail_store_id'], 'required'],
            [['instap_plan_id','retail_store_id', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['status'], 'string']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'instap_plan_id' => Yii::t('common', 'Instap Plan ID'),
            'retail_store_id' => Yii::t('common', 'Retail Store ID'),
            'status' => Yii::t('common', 'Status'),
            'created_at' => Yii::t('common', 'Created At'),
            'created_by' => Yii::t('common', 'Created By'),
            'updated_at' => Yii::t('common', 'Updated At'),
            'updated_by' => Yii::t('common', 'Updated By'),
        ];
    }

    public function getRetailStore() {
        return $this->hasOne(QcdRetailStore::className(), ['retail_store_id' => 'retail_store_id']);
    }
    public function getPlan() {
        return $this->hasOne(InstapPlan::className(), ['id' => 'instap_plan_id']);
    }


    public static function makeModel($instap_plan_id, $retail_store_id) {
        $m = new SELF();
        $m->instap_plan_id = $instap_plan_id;
        $m->retail_store_id = $retail_store_id;
        return $m;
    }
}
