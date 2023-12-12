<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use common\behaviors\MyAuditTrailBehavior;
use common\components\Utility;
use common\models\InstapPlanPool;
use common\models\UserPlanDetailEdit;
use common\models\UserPlanDetailEditHistory;
use api\components\CustomHttpException;
use common\behaviors\EditHistoryBehavior;


class UserPlanDetail extends \yii\db\ActiveRecord {
    public $notes;
    //TODO renove this scenario
    const SCENARIO_REQUIRE_NOTES = "scenario_require_notes";
    const SCENARIO_REQUIRE_PURCHASE_PRICE = "scenario_require_purchase_price";

    public static function tableName() {
        return 'user_plan_detail';
    }

    public function rules()
    {
        $r = SELF::userPlanDetailRules();
        $r2 = [
            ['notes', 'required', 'on'=> SELF::SCENARIO_REQUIRE_NOTES],
            ['notes', 'string', 'max' => 255, 'on'=> SELF::SCENARIO_REQUIRE_NOTES],
            ['sp_device_purchase_price', 'required', 'on'=> SELF::SCENARIO_REQUIRE_PURCHASE_PRICE],

            
        ];
        return array_merge($r,$r2);
    }
    //share with RegisterPlanForm
    public static function userPlanDetailRules() {
        return [
            [['plan_pool_id', 'sp_brand', 'sp_model_number', 'sp_serial', 'sp_imei', 'sp_color', 'sp_dealer_code', 'sp_country_of_purchase', 'sp_device_purchase_date', 'sp_device_capacity'], 'required'],
            [['plan_pool_id'], 'unique', 'targetClass'=> SELF::className()],
            [['plan_pool_id'], 'integer'],
            [['sp_device_purchase_price'], 'number'],
            [['sp_brand', 'sp_model_number', 'sp_serial', 'sp_imei', 'sp_color', 'sp_dealer_code', 'sp_country_of_purchase', 'sp_device_purchase_date', 'sp_device_capacity'], 'string', 'max' => 255],  
            //imei unique with plan id
            // [['sp_imei'], 'unique', 'targetAttribute' => ['sp_imei','plan_id'], 'targetClass'=> SELF::className(), 'message'=>'Duplicate imei number'],
            // //serial number unique with plan id, error message doesn't show but the rule of combine unique key is working
            // [['sp_serial'], 'unique', 'targetAttribute' => ['sp_serial', 'plan_id'], 'targetClass'=> SELF::className(), 'message'=>'Duplicate serial number'], 

            ['sp_imei', 'string', 'max'=>17, 'min'=> 15, 'message'=>'incorrect imei length'],
            ['sp_serial',function ($attribute, $params) {
                $s = strtoupper($this->sp_serial);
                $m = UserPlanDetail::find()->joinWith(['planPool'])->andWhere(['not in','instap_plan_pool.plan_status', InstapPlanPool::planStatusAllowForRegister()])->andWhere(['UPPER(user_plan_detail.sp_serial)' => $s])->andWhere(['not in', 'instap_plan_pool.id', $this->plan_pool_id])->count();
                if($m > 0) {
                    $this->addError($attribute, 'There is another plan registered with this serial number.');
                    return true;
                }
                return false;
            }],  
            ['sp_imei',function ($attribute, $params) {
                $s = strtoupper($this->sp_imei);
                $m = UserPlanDetail::find()->joinWith(['planPool'])->andWhere(['UPPER(user_plan_detail.sp_imei)' => $s])->andWhere(['not in','instap_plan_pool.plan_status', InstapPlanPool::planStatusAllowForRegister()])->andWhere(['not in', 'instap_plan_pool.id', $this->plan_pool_id])->count();
                if($m > 0) {
                    $this->addError($attribute, 'There is another plan registered with this IMEI.');
                    return true;
                }
                return false;
            }],    
        ];
    }

    // public function validateSerialNumber() {
    //     $plan_status_arr = InstapPlanPool::planStatusRequiredForRegistration();
    //     $m = SELF::find()->joinWith(['planPool' => function ($query) {
    //         $query->where(['not in','plan_status', $plan_status_arr]);
    //     }])->andWhere(['user_plan_detail.sp_serial' => $this->sp_serial])->all();
    //     if (!empty($m)) {
    //         $this->addError('token', Yii::t('app', 'Duplicate imei number.'));
    //         return false;
    //     }
    //     return true;
    // }

    public function validateImei() {
        if (empty($this->token) || !is_string($this->token)) {
            $this->addError('token', Yii::t('app', 'Verify token cannot be blank.'));
            return false;
        }
        $this->tokenModel = SysUserToken::find()
            ->notExpired()
            ->byType(SysUserToken::TYPE_EMAIL_ACTIVATION)
            ->byToken($this->token)
            ->one();

        if (!$this->tokenModel) {
            $this->addError('token', Yii::t('app', 'Wrong verify token.'));
            return false;
        }
        return true;
    }
    public function behaviors() {
        return [
            "timestamp" => TimestampBehavior::className(),
            "blame" => BlameableBehavior::className(),
            "auditTrail" => MyAuditTrailBehavior::className(),
            // "editTrail" => EditHistoryBehavior::className(),
        ];
    }  

    public function attributeLabels() {
        return [
            'id' => Yii::t('common', 'ID'),
            'plan_pool_id' => Yii::t('common', 'Plan Pool ID'),
            'sp_brand' => Yii::t('common', 'Smart Phone Brand'),
            'sp_model_number' => Yii::t('common', 'Smart Phone Model'),
            'sp_serial' => Yii::t('common', 'Smart Phone Serial'),
            'sp_imei' => Yii::t('common', 'IMEI'),
            'sp_color' => Yii::t('common', 'Smart Phone Color'),
            'sp_device_purchase_price' => Yii::t('common', 'Product Price'),
            
        ];
    }

    
    public static function find() {
        return new \common\models\query\UserPlanDetailQuery(get_called_class());
    }

    public static function makeModel($planPool, $form) {
        $m = SELF::find()->where(["plan_pool_id"=>$planPool->id])->one();
        if (!$m) { $m = new SELF(); }
        $m->plan_pool_id = $form->plan_pool_id;
        $m->plan_id = $planPool->plan_id;
        $m->sp_brand = strtoupper($form->sp_brand);
        $m->sp_model_number = $form->sp_model_number;
        $m->sp_model_name = self::getModelName($form->sp_brand, $form->sp_model_number);
        $m->sp_serial = strtoupper($form->sp_serial);
        $m->sp_imei = strtoupper($form->sp_imei);
        $m->sp_color = $form->sp_color;
        $m->sp_dealer_code = $form->sp_dealer_code;
        $m->sp_country_of_purchase = $form->sp_country_of_purchase;
        $m->sp_device_purchase_date = $form->sp_device_purchase_date;
        $m->sp_device_purchase_price = $form->sp_device_purchase_price;
        $m->sp_device_capacity = $form->sp_device_capacity;
        return $m;
    }

    public function toObject() {
        $d = $this;
        $o = (object) [];
        $o->sp_brand = $d->sp_brand;
        $o->sp_model_number = $d->sp_model_number;
        $o->sp_model_name = $d->sp_model_name;
        $o->sp_serial = $d->sp_serial;
        $o->sp_imei = $d->sp_imei;
        $o->sp_color = $d->sp_color;        
        $o->sp_dealer_code = $d->sp_dealer_code;
        $o->sp_country_of_purchase = $d->sp_country_of_purchase;
        $o->sp_device_purchase_date = $d->sp_device_purchase_date;
        $o->sp_device_purchase_price = $d->sp_device_purchase_price;
        $o->sp_device_capacity = $d->sp_device_capacity;
        return $o;

    }

    public static function getModelName($brand, $model_number) {
        $connection = Yii::$app->getDb();
        $model_name = "";
        $b = strtolower($brand);
        if($b == "apple") {
            $model_name = "";        
        } else {
            $modelName = $connection->createCommand('SELECT market_name FROM  qcd_device_model_lookup WHERE model = :model_number')
            ->bindParam(':model_number', $model_number)->queryOne();

            if($modelName){
                $model_name = $modelName['market_name'];
            }
        } 
        return $model_name;
    }

    public static function isPlanRegistered($planPoolModel) {
        $flag = false;
        // $setApproval = true;
        $model = UserPlanAction::find()->where(['plan_pool_id' => $planPoolModel->id])->asArray()->all();
        if(!empty($model)) {
            $actionArr = array_column($model, 'action_status');
            $match = in_array(UserPlanAction::ACTION_REGISTRATION, $actionArr);
            // $match2 = in_array(UserPlanAction::ACTION_PHYSICAL_ASSESSMENT, $actionArr);
            // $match3 = in_array(UserPlanAction::ACTION_UPLOAD_PHOTO, $actionArr);
            
            if($match) {
                $flag = true;
            }
            
        }
        return $flag;
    }


    public function getPlanPool()
    {
        return $this->hasOne(InstapPlanPool::className(), ['id' => 'plan_pool_id']);
    }

    public function getPlanDetailEdit()
    {
        return $this->hasOne(UserPlanDetailEdit::className(), ['plan_pool_id' => 'plan_pool_id']);
    }

    public function getPlanDetailEditHistory()
    {
        return $this->hasMany(UserPlanDetailEditHistory::className(), ['row_id' => 'plan_pool_id'])->
            orderBy(['created_at' => SORT_DESC]);
    }

    public function hasEdit()
    {
        $c = UserPlanDetailEdit::find()->where(['plan_pool_id'=> $this->plan_pool_id])->count();
        return $c > 0;
    }

    public function getNewEditedModel() {
        $model = new UserPlanDetailEdit();
        $model->sp_brand = $this->sp_brand;
        $model->sp_model_number = $this->sp_model_number;
        $model->sp_model_name = $this->sp_model_name;
        $model->sp_serial = $this->sp_serial;
        $model->sp_imei = $this->sp_imei;
        $model->sp_color = $this->sp_color;
        $model->notes = "";
        return $model; 
    }

    public function getEditHistory()
    {
        $arr = UserPlanDetailEditHistory::find()->orderBy(['created_at' => SORT_DESC])->where(['row_id'=> $this->plan_pool_id])->all();
        return $arr;
    }

    




    //html layout
    static public function getPlanDetailLayoutByModel($model) {
        // $model = $this;
        $b = strtolower($model->sp_brand);
        $html = "<table class='table'><thead><tr>";
        $html .= "<th width='*'>".Yii::t("common", "Device")."</th>";
        $html .= "<th width='*'>".Yii::t("common", "Model Name")."</th>";
        $html .= "<th width='*'>".Yii::t("common", "Model Number")."</th>";
        $html .= "<th width='*'>".Yii::t("common", "Serial Number")."</th>";
        $html .= "<th width='*'>".Yii::t("common", "IMEI Number")."</th>";
        $html .= "<th width='*'>".Yii::t("common", "Device Color")."</th>";
        $html .= "<th width='*'>".Yii::t("common", "Product Price")."</th>";
        //$html .= "<th width='100'>Device Capacity</th>";
        $html .= "</tr></thead>";
        $html .= "<tbody><tr>";
        $html .= "<td>" . $model->sp_brand . "</td>";
        $html .= "<td>" . (($b =="apple") ? "none" : $model->sp_model_name ). "</td>";
        $html .= "<td>" . $model->sp_model_number . "</td>";
        $html .= "<td>" . $model->sp_serial . "</td>";
        $html .= "<td>" . $model->sp_imei . "</td>";
        $html .= "<td>" . $model->sp_color . "</td>";
        $html .= "<td>" . ($model->sp_device_purchase_price ? $model->sp_device_purchase_price : 0). "</td>";
        $html .= "</tr></tbody></table>";

        return $html;
    }

    public function getPlanDetailSmallLayout() {
        $model = $this;
        
        $html = "<div style = 'display: inline-flex;'>";
        $html .= "<div style= 'margin-right: 15px;''float: left;'>";
        $html .= "<b>Brand   : </b>".$model->sp_brand."<br>";
        $html .= "<b>Model      : </b>".$model->sp_model_number."<br>";
        $html .= "<b>S/N        :</b> ". $model->sp_serial."<br>";
        $html .= "</div>";
        $html .= "<div style=  'padding: 0rem;''float: left;'>";
        $html .= "<b>IMEI       : </b>". $model->sp_imei."<br>";
        $html .= "<b>Colour     : </b>".$model->sp_color;
        $html .= "</div>";
        $html .="</div>";
                            
        return $html; 

    }



                            


}

