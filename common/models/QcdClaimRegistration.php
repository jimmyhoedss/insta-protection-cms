<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "qcd_claim_registration".
 *
 * @property string $claim_number
 * @property int $plan_pool_id
 * @property string $policy_number
 * @property int $claim_type_id
 * @property int $policy_plan_id
 * @property int $device_id
 * @property int $device_color_id
 * @property string $serial_number
 * @property string $imei
 * @property string $device_issue
 * @property string $remarks
 * @property int $repair_centre_id
 * @property int $has_courier
 * @property int $courier_payer_id
 * @property string $first_name
 * @property string $last_name
 * @property string $mobile_number
 * @property string $email
 * @property string $company_name
 * @property string $address
 * @property string $address_delivery
 */
class QcdClaimRegistration extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'qcd_claim_registration';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['claim_number', 'policy_number', 'claim_type_id', 'policy_plan_id', 'device_id', 'device_color_id', 'serial_number', 'imei', 'device_issue', 'repair_centre_id', 'first_name', 'last_name', 'mobile_number', 'email', ], 'required'],
            [['claim_type_id', 'policy_plan_id', 'device_id', 'device_color_id', 'repair_centre_id', 'has_courier', 'courier_payer_id'], 'integer'],
            [['device_issue', 'remarks', 'address', 'address_delivery'], 'string'],
            [['policy_number', 'serial_number', 'imei', 'first_name', 'last_name', 'mobile_number', 'email', 'company_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'claim_number' => Yii::t('app', 'Claim Number'),
            'plan_pool_id' => Yii::t('app', 'Plan Pool ID'),
            'policy_number' => Yii::t('app', 'Policy Number'),
            'claim_type_id' => Yii::t('app', 'Claim Type ID'),
            'policy_plan_id' => Yii::t('app', 'Policy Plan ID'),
            'device_id' => Yii::t('app', 'Device ID'),
            'device_color_id' => Yii::t('app', 'Device Color ID'),
            'serial_number' => Yii::t('app', 'Serial Number'),
            'imei' => Yii::t('app', 'Imei'),
            'device_issue' => Yii::t('app', 'Device Issue'),
            'remarks' => Yii::t('app', 'Remarks'),
            'repair_centre_id' => Yii::t('app', 'Repair Centre ID'),
            'has_courier' => Yii::t('app', 'Has Courier'),
            'courier_payer_id' => Yii::t('app', 'Courier Payer ID'),
            'first_name' => Yii::t('app', 'First Name'),
            'last_name' => Yii::t('app', 'Last Name'),
            'mobile_number' => Yii::t('app', 'Mobile Number'),
            'email' => Yii::t('app', 'Email'),
            'company_name' => Yii::t('app', 'Company Name'),
            'address' => Yii::t('app', 'Address'),
            'address_delivery' => Yii::t('app', 'Address Delivery'),
        ];
    }

    public static function makeModel($data) {

        $m = new QcdClaimRegistration();
        $m->claim_number = $data['claim_number']; 
        $m->policy_number = $data['policy_number']; 
        $m->claim_type_id = $data['claim_type_id']; 
        $m->policy_plan_id = $data['policy_plan_id']; 
        $m->policy_plan_band_id = $data['policy_plan_band_id']; 
        $m->device_id = $data['device_id']; 
        $m->device_color_id = $data['device_color_id']; 
        $m->serial_number = $data['serial_number']; 
        $m->imei = $data['imei']; 
        $m->device_issue = $data['device_issue']; 
        $m->remarks = $data['remarks']; 
        $m->repair_centre_id = $data['repair_centre_id']; 
        $m->first_name = $data['first_name']; 
        $m->last_name = $data['last_name']; 
        $m->mobile_number = $data['mobile_number']; 
        $m->email = $data['email']; 
        // $m->company_name = $data['company_name']; 
        // $m->address = $data['address']; 
        // $m->address_delivery = $data['address_delivery']; 
        return $m;
    }

    public function getQcdDeviceInfo($brand) {

        $b = strtolower($brand);
        $connection = Yii::$app->getDb();
        //compare brand 
        $dm = $connection->createCommand('SELECT device_maker_id FROM  qcd_device_maker WHERE LOWER(device_maker) = :device_maker ')
        ->bindParam(':device_maker', $b)->queryOne();

        //compare device model
        if($dm){
            $dmi = $dm['device_maker_id'];
            //get repair centre
            $repair_centre = $connection->createCommand('SELECT * FROM  qcd_repair_centre WHERE device_maker_id = :device_maker_id ')->bindParam(':device_maker_id', $dmi)->queryAll();
        }else{
            $str = "cannot find brand";
            throw new CustomHttpException($str, CustomHttpException::UNPROCESSABLE_ENTITY);
        }
        Yii::$app->api->sendSuccessResponse($repair_centre);

    }

    public function getQcdDeviceColour($brand) {

    }

    public function getQcdDeviceCapacity($brand) {
        
    }
    
    public static function getQcdRepairCentre($brand, $country_code) {

        $b = strtolower($brand);
        $connection = Yii::$app->getDb();
        $repair_centre = [];
        //compare brand 
        $dm = $connection->createCommand('SELECT device_maker_id FROM  qcd_device_maker WHERE LOWER(device_maker) = :device_maker ')
        ->bindParam(':device_maker', $b)->queryOne();
        if($dm){
            $dmi = $dm['device_maker_id'];
            //get repair centre
            $repair_centre = $connection->createCommand('SELECT * FROM  qcd_repair_centre WHERE device_maker_id = :device_maker_id ')->bindParam(':device_maker_id', $dmi)->queryAll();
        }else{
            $str = Yii::t('common',"cannot find brand");
            throw new CustomHttpException($str, CustomHttpException::UNPROCESSABLE_ENTITY);
        }
        return $repair_centre;

    }
}
