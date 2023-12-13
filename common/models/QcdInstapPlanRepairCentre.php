<?php

namespace common\models;

use Yii;
use common\models\QcdRepairCentre;
use common\models\InstapPlan;
use common\components\MyCustomActiveRecord;

class QcdInstapPlanRepairCentre extends MyCustomActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'qcd_instap_plan_repair_centre';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['instap_plan_id', 'repair_centre_id'], 'required'],
            [['instap_plan_id','repair_centre_id', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
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
            'repair_centre_id' => Yii::t('common', 'Repair Centre ID'),
            'status' => Yii::t('common', 'Status'),
            'created_at' => Yii::t('common', 'Created At'),
            'created_by' => Yii::t('common', 'Created By'),
            'updated_at' => Yii::t('common', 'Updated At'),
            'updated_by' => Yii::t('common', 'Updated By'),
        ];
    }

    public function getRepairCentre() {
        return $this->hasOne(QcdRepairCentre::className(), ['repair_centre_id' => 'repair_centre_id']);
    }
    public function getPlan() {
        return $this->hasOne(InstapPlan::className(), ['id' => 'instap_plan_id']);
    }


    public static function makeModel($instap_plan_id, $repair_centre_id) {
        $m = new SELF();
        $m->instap_plan_id = $instap_plan_id;
        $m->repair_centre_id = $repair_centre_id;
        return $m;
    }
}
