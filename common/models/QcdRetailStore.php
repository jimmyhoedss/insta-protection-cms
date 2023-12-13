<?php

namespace common\models;

use Yii;
use common\models\QcdDeviceMaker;
use common\models\QcdDeviceMakerRetailStore;
use common\models\QcdRetailStore;
use common\components\MyCustomActiveRecord;
use yii\validators\EmailValidator;



class QcdRetailStore extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'qcd_retail_store';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['is_service_hub', 'is_courier', 'device_maker_id', 'is_asp'], 'integer'],
            [['address'], 'string'],
            [['retail_store', 'state_name', 'city_name', 'opening_hours', 'email', 'telephone', 'state'], 'string', 'max' => 255],
            [['retail_store', 'opening_hours', 'email', 'address'], 'required'],
            [['country_code'], 'string', 'max' => 3],
            [['state_code'], 'string', 'max' => 10],
            ['email', 'validateMultipleEmail'],
        ];
    }

    public function validateMultipleEmail($attribute, $param) {
        //use to validate multiple email. eg abc@gmail.com,abc@gmail.com
        $validator = new EmailValidator();
        $trimEmail = str_replace(' ', '', $this->email);
        $emailArr = explode(',', $trimEmail);
        foreach ($emailArr as $email) {
            if (!$validator->validate($email, $error)) {
                $this->addError($attribute, $error);
            }
        }
       
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'retail_store' => Yii::t('common', 'Retail Store'),
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

    public static function listRetailStore($brand, $region_id, $plan_id) {
       
        if(isset($brand) != null) {
            $brand_id = $brand->device_maker_id;
            $retail_stores = QcdDeviceMakerRetailStore::find()->where(['device_maker_id' => $brand_id])->asArray()->all();

            if(!empty($retail_stores)) {
                $retail_store_id_arr = array_column($retail_stores, 'retail_store_id');
                $retail_stores = QcdInstapPlanRetailStore::find()->where(['instap_plan_id' => $plan_id])->andWhere(['in', 'retail_store_id', $retail_store_id_arr])->asArray()->all();
                $retail_store_id_arr = array_column($retail_stores, 'retail_store_id');
                $rc = SELF::find()->where(['in', 'id', $retail_store_id_arr])->andWhere(['country_code' => $region_id, 'status' => MyCustomActiveRecord::STATUS_ENABLED])->all();

                return $rc;
            }
            
        } else {
           return null;
        }
        
    }

    //*********** html layout ***********  

    public function getBrandLayout($retail_store_arr) {
        $html = "";
        foreach ($retail_store_arr as $retail_store) {
            $html .= "<div class='brand'>" . $retail_store->brand->device_maker. "</div>";
        }
        return $html;
    }

    public function getPlanLayout($retail_store_arr) {
        $html = "";
        foreach ($retail_store_arr as $retail_store) {
            $html .= "<div class='brand'>" . $retail_store->plan->name. "</div>";
        }
        return $html;
    }

    public function toObject() {
        $m = $this;
        $o = (object) [];

        $o->retail_store = $m->retail_store;
        $o->address = $m->address;    

        return $o;
    }
}
