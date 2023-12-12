<?php

namespace common\models;

use Yii;

class QcdDeviceMaker extends \yii\db\ActiveRecord
{
    
    public static function tableName()
    {
        return 'qcd_device_maker';
    }

    public function rules()
    {
        return [
            [['device_maker_id', 'device_maker'], 'required'],
            [['device_maker_id'], 'integer'],
            [['device_maker'], 'string', 'max' => 255],
        ];
    }
    
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'device_maker_id' => Yii::t('common', 'Device Maker ID'),
            'device_maker' => Yii::t('common', 'Device Maker'),
        ];
    }
}
