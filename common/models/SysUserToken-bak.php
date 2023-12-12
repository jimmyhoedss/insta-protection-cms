<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use common\commands\SendSmsCommand;
use common\jobs\SmsQueueJob;
use common\jobs\EmailQueueJob;
use common\models\form\OtpForm;

class SysUserToken extends \yii\db\ActiveRecord
{
    const TOKEN_LENGTH = 40;
    const TYPE_EMAIL_ACTIVATION = 'email_activation';
    const TYPE_PASSWORD_RESET = 'password_reset';
    const TYPE_ONE_TIME_PASSWORD_API = 'one_time_password_api';
    const TYPE_ONE_TIME_PASSWORD_CMS = 'one_time_password_cms';

    public $user;

    public static function tableName() {
        return 'sys_user_token';
    }

    public function behaviors() {
        return [
            TimestampBehavior::className()
        ];
    }

    public function rules() {
        return [
            [['user_id', 'token'], 'required'],
            [['user_id', 'expire_at', 'created_at', 'updated_at'], 'integer'],
            [['type'], 'string', 'max' => 64],
            [['token'], 'string', 'max' => 64],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            //[['token'], 'unique'],
        ];
    }

    public function attributeLabels() {
        return [
            'id' => Yii::t('common', 'ID'),
            'user_id' => Yii::t('common', 'User ID'),
            'type' => Yii::t('common', 'Type'),
            'token' => Yii::t('common', 'Token'),
            'expire_at' => Yii::t('common', 'Expire At'),
            'created_at' => Yii::t('common', 'Created At'),
            'updated_at' => Yii::t('common', 'Updated At'),
        ];
    }

    public function getUser() {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
    public function getUserData() {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    private function getValidityDuration() {
        // durations in seconds
        $one_minute = 60;
        $one_hour = 3600;
        $one_day = 86400;
        $durations = Array(
            SELF::TYPE_EMAIL_ACTIVATION => $one_day * 2,
            SELF::TYPE_PASSWORD_RESET => $one_hour,
            SELF::TYPE_ONE_TIME_PASSWORD_API => $one_minute * 5,
            //SELF::TYPE_ONE_TIME_PASSWORD_API => 3,
            SELF::TYPE_ONE_TIME_PASSWORD_CMS => $one_minute * 5,
        );
        return (isset($durations[$this->type])) ? $durations[$this->type] : $one_minute;
    }

    public function isTokenExpired() {
        $timeNow = time();
        return $timeNow > $this->expire_at;    
    }
    
    public function getCooldown() {
        $diff = $this->expire_at - time();
        return $diff;
    }

    public function sendEmail(){
        // need to check for colddown at here to prevent brute force attack
        Yii::$app->queue->delay(0)->push(new EmailQueueJob([
            'subject' => Yii::t('frontend', '{app-name} | One-Time Password', ['app-name'=>Yii::$app->name]),
            'view' => 'sendOtp',
            'to' => $this->user->email,
            'params' => [
                'token' => $this->token
            ]
        ]));
        $emailTrace = SysSesTrace::makeModel($this->user->email);
        $emailTrace->save();
    }

    public function sendSms(){
        $mobile_number_full = $this->user->mobile_number_full;
        $message = "Use " . $this->token . " One-Time Password for InstaProtection within 5 minutes. Thank you.";
        /*
        Yii::$app->commandBus->handle(new SendSmsCommand([
            'mobileNumber' => $mobile_number_full,
            'message' => $message,
        ]));
        */
        //save $$ first, make token 111111 for development
        
        Yii::$app->queue->delay(0)->push(new SmsQueueJob([            
            'mobileNumber' => $mobile_number_full,
            'message' => $message,
        ]));
        
    }

    public static function getExisitingToken($user, $type) {
        //$timeNow = time();
        $query = SELF::find();
        $query->where(['user_id'=>$user->id]);
        $query->andWhere(['type'=>$type]);
        //$query->andWhere(['>', 'expire_at', $timeNow]);
        $query->orderBy(['created_at'=>SORT_DESC]);
        return $query->one();
    }

    public static function deleteAllUserToken($user, $type) {
        SELF::deleteAll(['AND', 'user_id = :user_id', 'type = :type'], [':user_id' => $user->id, ':type' => $type]);
    }

    public static function makeModel($user, $type){
        $m = new SELF();
        $m->user = $user;
        $m->user_id = $user->id;
        $m->type = $type;
        $token = "";
        switch ($type) {
            case SELF::TYPE_EMAIL_ACTIVATION:
                $token = Yii::$app->security->generateRandomString(SELF::TOKEN_LENGTH);
                break;

            case SELF::TYPE_ONE_TIME_PASSWORD_API:

            case SELF::TYPE_ONE_TIME_PASSWORD_CMS:
            	$token = (string) random_int(100001,999999);
                //$token = (string) 111111;
                break;
            
            default:
                # code...
                break;
        }
        $m->token = $token;
        $m->expire_at = time() + $m->getValidityDuration();
        return $m;
    }

    public static function find()
    {
        return new \common\models\query\SysUserTokenQuery(get_called_class());
    }


}