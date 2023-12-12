<?php

namespace common\models;

use Yii;
use common\models\QcdRepairCentre;
use common\models\QcdDeviceMaker;
use common\components\MyCustomActiveRecord;


class QcdDeviceMakerRepairCentre extends MyCustomActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'qcd_device_maker_repair_centre';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['device_maker_id', 'repair_centre_id'], 'required'],
            [['device_maker_id','repair_centre_id', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
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
            'device_maker_id' => Yii::t('common', 'Device Maker ID'),
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
    public function getBrand() {
        return $this->hasOne(QcdDeviceMaker::className(), ['device_maker_id' => 'device_maker_id']);
    }


    public static function makeModel($device_maker_id, $repair_centre_id) {
        $m = new SELF();
        $m->device_maker_id = $device_maker_id;
        $m->repair_centre_id = $repair_centre_id;
        return $m;
    }
}
