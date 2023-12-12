<?php
namespace common\models\form;

use cheatsheet\Time;
use common\models\User;
use common\models\UserToken;
use common\models\fcm\SysFcmMessage;
use common\models\fcm\FcmOtp;
use yii\base\InvalidParamException;
use yii\base\Model;
use Yii;

class RewardGeneratePoolForm extends Model
{
    public $reward_id;
    public $amount;
    public $form_step = 0;
    public $otp;

    public function rules()
    {
        return [
            [['reward_id', 'amount', 'otp'], 'required'],
            [['otp'], 'string', 'min' => 6, 'max' => 6],
            ['form_step', 'integer'],
            [['amount'], 'integer', 'min' => 1],
        ];
    }

    public function validateToken() {
        $uid = Yii::$app->user->id;
        $m = UserToken::find()->orderBy(['created_at'=>SORT_DESC])->andWhere(['user_id'=>$uid])->andWhere(['type'=>UserToken::TYPE_ONE_TIME_PASSWORD_REWARD])->one();

        if($m == null || $m->token !== $this->otp){
            $this->addError('otp', Yii::t('frontend', 'Invalid token.'));
            return false;
        } else if(self::isTokenExpired($m->created_at)){
            //check within 3 mins
            $this->addError('otp', Yii::t('frontend', 'Token expired.'));
            return false;
        }
        
        $m->delete();
        return true;
    }

    public function attributeLabels()
    {
        return [
            'reward_id'=>'Reward Id',
            'amount'=>'Amount',
            'form_step'=>'Form Step',
            'otp'=>'One-Time Pin'
        ];
    }

    public function isStepOne(){
        return $this->form_step == 0;
    }

    public function setStepOne(){        
        return $this->form_step = 0;
    }

    public function isStepTwo(){
        return $this->form_step == 1;
    }

    public function setStepTwo(){        
        return $this->form_step = 1;
    }

    public function sendOtp(){
        $user = Yii::$app->user->identity;

        if (self::isRequested($user)){
            //check for any previous record
            $this->addError('otp', Yii::t('frontend', 'Please try again in 1 minute for a new OTP.'));
            return;
        }

        $rand_num = random_int(100001,999999);
        $isSaved = UserToken::saveOtp($user->id, UserToken::TYPE_ONE_TIME_PASSWORD_REWARD, $rand_num, Time::SECONDS_IN_A_MINUTE);
        if($isSaved){            
            $fcm = new FcmOtp($user, $rand_num);
            $fcm->send();
        }
        return;
    }

    private function isRequested($model){
        $m = UserToken::find()->orderBy(['created_at'=>SORT_DESC])->andWhere(['user_id'=>$model->id])->andWhere(['type'=>UserToken::TYPE_ONE_TIME_PASSWORD_REWARD])->one();

        if($m != null){
            if(!self::isTokenExpired($m->created_at)){
                return true;
            } else {
                //delete previous record
                $m->delete();
            }
        }
        return false;
    }

    private function isTokenExpired($previousCreatedAt){   
        $date = date_create();
        $timeDifference = (date_timestamp_get($date) - $previousCreatedAt)/60; //in mins
        
        //echo $timeDifference;   
        if ($timeDifference < 1){
            return false;  
        }
        return true;
    }
}