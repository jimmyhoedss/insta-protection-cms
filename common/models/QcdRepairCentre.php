<?php

namespace common\models;

use Yii;
use common\models\QcdDeviceMaker;
use common\models\QcdDeviceMakerRepairCentre;
use common\models\QcdRepairCentre;
use common\components\MyCustomActiveRecord;



class QcdRepairCentre extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'qcd_repair_centre';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['is_service_hub', 'is_courier', 'device_maker_id', 'is_asp'], 'integer'],
            [['address'], 'string'],
            [['repair_centre', 'state_name', 'city_name', 'opening_hours', 'email', 'telephone', 'state'], 'string', 'max' => 255],
            [['country_code'], 'string', 'max' => 3],
            [['state_code'], 'string', 'max' => 10],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'repair_centre' => Yii::t('common', 'Repair Centre'),
            'country_code' => Yii::t('common', 'Country Code'),
            'state_code' => Yii::t('common', 'State Code'),
            'state_name' => Yii::t('common', 'State Name'),
            'city_name' => Yii::t('common', 'City Name'),
            'address' => Yii::t('common', 'Address'),
            'opening_hours' => Yii::t('common', 'Opening Hours'),
            'email' => Yii::t('common', 'Email'),
            'telephone' => Yii::t('common', 'Telephone'),
            'is_service_hub' => Yii::t('common', 'Is Service Hub'),
            'is_courier' => Yii::t('common', 'Is Courier'),
            'device_maker_id' => Yii::t('common', 'Device Maker ID'),
            'is_asp' => Yii::t('common', 'Is Asp'),
            'state' => Yii::t('common', 'State'),
        ];
    }

     public static function listRepairCentre($brand, $region_id) {
       
        if(isset($brand) != null) {
            $brand_id = $brand->device_maker_id;
            $repair_centres = QcdDeviceMakerRepairCentre::find()->where(['device_maker_id' => $brand_id])->asArray()->all();

            if(!empty($repair_centres)) {
                $repair_centre_id_arr = array_column($repair_centres, 'repair_centre_id');
                $rc = SELF::find()->where(['in', 'id', $repair_centre_id_arr])->andWhere(['country_code' => $region_id, 'status' => MyCustomActiveRecord::STATUS_ENABLED])->all();

                return $rc;
            }
            
        } else {
           return null;
        }
        
    }

    //*********** html layout ***********  

    public function getBrandLayout($repair_centre_arr) {
        $html = "";
        foreach ($repair_centre_arr as $repair_centre) {
            $html .= "<div class='brand'>" . $repair_centre->brand->device_maker. "</div>";
        }
        return $html;
    }

    public function toObject() {
        $m = $this;
        $o = (object) [];

        $o->repair_centre = $m->repair_centre;
        $o->address = $m->address;    

        return $o;
    }
}
