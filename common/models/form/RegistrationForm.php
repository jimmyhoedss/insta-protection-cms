<?php
namespace common\models\form;

use Yii;
use yii\base\Model;
use common\models\User;
use common\models\UserProfile;
use common\components\Utility;
use api\components\CustomHttpException;

class RegistrationForm extends Model
{
    public $region_id;
    public $mobile_calling_code;
    public $mobile_number;
    public $mobile_number_full;

    public function rules()
    {
        return SELF::registrationRules();
    }
    //share with OtpForm
    public static function registrationRules() {
        return [
            [['region_id', 'mobile_calling_code', 'mobile_number', 'mobile_number_full'], 'required', 'except'=>'resend'],
            [['mobile_number_full'], 'required', 'on'=>'resend'],
            [['region_id'], 'string'],
            [['region_id'], 'string', 'min' => 2, 'max' => 2],
            [['mobile_calling_code', 'mobile_number', 'mobile_number_full'], 'string'],
            [['mobile_calling_code'], 'string', 'min' => 1, 'max' => 6], // USA code is 1
            ['mobile_calling_code', 'match', 'pattern' => '/^[0-9]+$/'],
            [['mobile_number'], 'string', 'min' => 8, 'max' => 20],
            ['mobile_number', 'match', 'pattern' => '/^[0-9]+$/'],
            [['mobile_number_full'], 'string', 'min' => 8, 'max' => 30],
            ['mobile_number_full', 'match', 'pattern' => '/^[0-9]+$/'],
        ];
    }


    public function attributeLabels()
    {
        return SELF::registrationAttributeLabels();
    }
    public static function registrationAttributeLabels() {
        return [
            'region_code' => Yii::t('common', 'Region Code'),
            'mobile_calling_code' => Yii::t('common', 'Mobile Calling Code'),
            'mobile_number' => Yii::t('common', 'Mobile Number'),
            'mobile_number_full' => Yii::t('common', 'Mobile Number'),
       
        ];
    }




    public function register()
    {
        if ($this->validate()) {
            $success = true;
            $transaction = Yii::$app->db->beginTransaction();

            $user = new User();
            $user->region_id = $this->region_id;
            $user->mobile_calling_code = $this->mobile_calling_code;
            $user->mobile_number = $this->mobile_number;
            $user->mobile_number_full = $this->mobile_number_full;
            $user->provisional_token = User::generateProvisionalToken();

            try {

                $user->save();
                $user->afterSignup();            
                $transaction->commit();
                return $user;

            } catch (yii\db\IntegrityException $e) {

                $transaction->rollback();
                throw CustomHttpException::internalServerError(Yii::t('common', "Cannot create user."));

            }

        }

        return null;
    }

}
