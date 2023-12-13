<?php

namespace common\models;

use Yii;
use common\models\QcdRetailStore;
use common\models\QcdDeviceMaker;
use common\components\MyCustomActiveRecord;

class QcdDeviceMakerRetailStore extends MyCustomActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'qcd_device_maker_retail_store';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['device_maker_id', 'retail_store_id'], 'required'],
            [['device_maker_id','retail_store_id', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
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
    public function getBrand() {
        return $this->hasOne(QcdDeviceMaker::className(), ['device_maker_id' => 'device_maker_id']);
    }


    public static function makeModel($device_maker_id, $retail_store_id) {
        $m = new SELF();
        $m->device_maker_id = $device_maker_id;
        $m->retail_store_id = $retail_store_id;
        return $m;
    }
}
