<?php

namespace common\models;

use Yii;

class QcdDeviceColor extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'qcd_device_color';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['device_color_id', 'device_color', 'device_model_id'], 'required'],
            [['device_color_id', 'device_model_id'], 'integer'],
            [['device_color'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'device_color_id' => Yii::t('common', 'Device Color ID'),
            'device_color' => Yii::t('common', 'Device Color'),
            'device_model_id' => Yii::t('common', 'Device Model ID'),
        ];
    }
}
