<?php

namespace common\models;

use Yii;


class QcdDeviceModel extends \yii\db\ActiveRecord
{
    
    public static function tableName()
    {
        return 'qcd_device_model';
    }

    public function rules()
    {
        return [
            [['device_model_id', 'device_maker_id', 'device_type_id', 'device_model'], 'required'],
            [['device_model_id', 'device_maker_id', 'device_type_id'], 'integer'],
            [['device_model'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'device_model_id' => Yii::t('common', 'Device Model ID'),
            'device_maker_id' => Yii::t('common', 'Device Maker ID'),
            'device_type_id' => Yii::t('common', 'Device Type ID'),
            'device_model' => Yii::t('common', 'Device Model'),
        ];
    }
}
