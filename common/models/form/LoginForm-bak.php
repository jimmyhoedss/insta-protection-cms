<?php
namespace common\models\form;

use cheatsheet\Time;
use common\models\User;
use common\models\SysUserToken;
use common\models\SysFcmMessage;
use yii\base\InvalidParamException;
use Yii;
use yii\base\Model;
use common\components\MyCustomModel;
use api\components\CustomForbiddenHttpException;
use api\components\CustomHttpException;
use common\components\MyCustomActiveRecord;
use yii\web\ForbiddenHttpException;

/**
 * Login form
 */
class LoginForm extends MyCustomModel
{
    public $mobile_calling_code;
    public $mobile_number;
    public $mobile_number_full;
    public $token;
    public $admin_email;
    public $password;
    //public $form_step = 0;

    //public $user = null;

    const EMAIL_LOGIN = "email_login";
    const CMS_LOGIN = "cms_login";
    const API_LOGIN = "api_login"; //app
    const OTP_SCREEN = "otp_screen";

    public function rules() {
        return [
            [['admin_email'], 'required', 'on' => SELF::EMAIL_LOGIN],
            ['admin_email', 'email'],
            ['password', 'validatePassword', 'on' => SELF::EMAIL_LOGIN],
            [['mobile_number_full', 'token'], 'required', 'on' => SELF::CMS_LOGIN],
            [['mobile_number_full', 'token'], 'required', 'on' => SELF::API_LOGIN],
            [['mobile_calling_code', 'mobile_number', 'mobile_number_full','password'], 'string'],
            [['mobile_calling_code'], 'string', 'min' => 1, 'max' => 6], // USA code is 1
            ['mobile_calling_code', 'match', 'pattern' => '/^[0-9]+$/'],
            [['mobile_number'], 'string', 'min' => 8, 'max' => 20],
            ['mobile_number', 'match', 'pattern' => '/^[0-9]+$/'],
            [['mobile_number_full'], 'string', 'min' => 8, 'max' => 30],
            ['mobile_number_full', 'match', 'pattern' => '/^[0-9]+$/'],

            [['token'], 'string', 'min' => 6, 'max' => 6],
            
            //['form_step', 'number'],
            

        ];
    }

    public function attributeLabels() {
        return [
            'mobile_calling_code'=>Yii::t('common', 'Mobile Calling Code'),
            'mobile_number'=>Yii::t('common', 'Mobile Number'),
            'mobile_number_full'=>Yii::t('common', 'Full Mobile Number'),
            'token'=>Yii::t('common', 'Token'),
        ];
    }

    public function validatePassword()
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError('password', Yii::t('backend', 'Incorrect username or password.'));
            }
        }
    }

    public function isValidAccount($user) {
        $valid = true;

        if($user->account_status == User::ACCOUNT_STATUS_EXCEED_MAX_LOGIN_ATTEMPT) {
            $msg =  Yii::t('common',"Exceeded login attempt limit, this account has been suspended.");
            $key = CustomHttpException::KEY_ACCOUNT_OVER_MAX_LOGIN_ATTEMPT;
            $this->addError('mobile_number_full', $msg, $key);
            
            $valid = false;
        }
        if ($user->account_status == User::ACCOUNT_STATUS_SUSPENDED) {
            $msg = Yii::t("common", "This account has been suspended");
            $key = CustomHttpException::KEY_ACCOUNT_SUSPENDED;
            $this->addError('mobile_number_full', $msg, $key);
            
            $valid = false;
        }
        /*if ($user->email_status == User::EMAIL_STATUS_NOT_VERIFIED) {
            $msg = "Your email address must be verified before you can log in.";
            $key = CustomHttpException::KEY_EMAIL_NOT_VERIFIED;
            $this->addError('mobile_number_full', Yii::t('frontend', $msg));            
            return false;
        }*/
        if ($user->status == MyCustomActiveRecord::STATUS_DISABLED) {
            $msg = Yii::t('common', "Your account is disabled.");
            $key = CustomHttpException::KEY_ACCOUNT_DISABLED;
            $this->addError('mobile_number_full', $msg, $key);
            
            $valid = false;
        }

        return $valid;
    }    
    public function validateToken($user, $type){
        //$m = SysUserToken::find()->andWhere(['token'=>$this->token])->andWhere(['user_id'=>$this->user->id])->andWhere(['type'=>$type])->one();
        $model = SysUserToken::getExisitingToken($user, $type);
        if ($model == null) {
            $this->addError('token', Yii::t('common', 'No token record.'), CustomHttpException::KEY_INVALID_OR_EXPIRED_TOKEN);
            return false;
        }
        if ($model->isTokenExpired()){
            $this->addError('token', Yii::t('common', 'Token expired.'), CustomHttpException::KEY_INVALID_OR_EXPIRED_TOKEN);
            return false;
        }
        if ($model->token != $this->token) {
            $this->addError('token', Yii::t('common', 'Invalid token.'), CustomHttpException::KEY_INVALID_OR_EXPIRED_TOKEN);
            return false;   
        }
        return $model;
    }

    public function loginApi() {
        if ($this->validate()) {
            $user = User::findByFullMobileNumber($this->mobile_number_full);
            if ($user) {
                $validToken = $this->validateToken($user, SysUserToken::TYPE_ONE_TIME_PASSWORD_API);
                if ($validToken && $this->isValidAccount($user)) {
                    $user->updateAttributes(['mobile_status' => User::MOBILE_STATUS_VERIFIED]);
                    $validToken->delete();
                    $user->touch('login_at');
                    Yii::$app->user->login($user);
                    return $user;
                }
                
                //loynote: shd we do a cooldown until allow next attempt instead?
                //$this->increaseLoginAttempt($user);
                //print_r($this->errors);
                //exit();
            } else {
                $this->addError('mobile_number_full', Yii::t('common', 'No such user.'), CustomHttpException::KEY_INVALID_CREDENTIALS);
            }
        }
        return null;
    }

    public function loginBackend(){
        if ($this->validate()) {
            $user = User::findByFullMobileNumber($this->mobile_number_full);
            if ($user && Yii::$app->authManager->checkAccess($user->id, User::PERMISSION_LOGIN_TO_CMS)) {
                $validToken = $this->validateToken($user, SysUserToken::TYPE_ONE_TIME_PASSWORD_CMS);
                if ($validToken && $this->isValidAccount($user)) {
                    $validToken->delete();
                    Yii::$app->user->login($user);
                    return $user;
                }
                //$this->increaseLoginAttempt($user);
            } else {
                $this->addError('mobile_number', Yii::t('common', 'No such user.'), CustomHttpException::KEY_INVALID_CREDENTIALS);
            }
        }
        return null;
    }

    public function loginDashboard(){
        if ($this->validate()) {
            $user = User::findByFullMobileNumber($this->mobile_number_full);
            //loynote: only admin can login CMS
            if ($user && $user->can(User::ROLE_USER)) {
                $roles = Yii::$app->authManager->getRolesByUser($user->id);

                $validToken = $this->validateToken($user, SysUserToken::TYPE_ONE_TIME_PASSWORD_CMS);
                if ($validToken && $this->isValidAccount($user)) {
                    $validToken->delete();
                    Yii::$app->user->login($user);
                    return $user;
                }
                //$this->increaseLoginAttempt($user);
            } else {
                $this->addError('mobile_number', Yii::t('common', 'No such user.'), CustomHttpException::KEY_INVALID_CREDENTIALS);
            }
        }
        return null;
    }

    private function increaseLoginAttempt($user){
        $user->updateCounters(['login_attempt' => 1]);
        if($user->login_attempt >= User::MAX_LOGIN_ATTEMPTS){
            $user->updateAttributes(['account_status' => User::ACCOUNT_STATUS_EXCEED_MAX_LOGIN_ATTEMPT, 'login_attempt' => 0]);
        }
    }

    
    


    /*
    private function checkIsAdmin($roles) {
        foreach ($roles as $key => $value) {
            echo $key;
            //print_r($roles[$key]);
        }
        //User::ROLE_ADMINISTRATOR
    }
    */

}