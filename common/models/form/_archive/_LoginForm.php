<?php
namespace common\models\form;

use cheatsheet\Time;
use common\models\User;
use common\models\UserToken;
use common\models\SysFcmMessage;
use yii\base\InvalidParamException;
use Yii;
use yii\base\Model;
use api\components\CustomForbiddenHttpException;
use api\components\CustomHttpException;
use common\components\MyCustomActiveRecord;
use yii\web\ForbiddenHttpException;

/**
 * Login form
 */
class LoginForm extends Model
{
    public $email;
    public $password;
    public $rememberMe = true;

    public $token = "100000";
    public $form_step = 0;

    private $user = null;

    public function rules()
    {
        return [
            [['email', 'password'], 'required'],
            ['email', 'email'],
            //loynote: login not validating pwd complexity to facilitate development...
            //[['password'], 'string', 'min' => 8, 'max' => 128],
            //['password', 'match', 'pattern' => '/^(?=.*[0-9])(?=.*[A-Z])([a-zA-Z0-9!@#$%^&*()]+)$/', 'message' => 'Your password require at least one upper-case letter and at least one digit'],
            ['token', 'required', 'on' => 'cms_login'],
            [['token'], 'string', 'min' => 6, 'max' => 6],
            ['form_step', 'integer'],
            ['rememberMe', 'boolean'],
            ['password', 'validatePassword']
        ];
    }

    public function attributeLabels()
    {
        return [
            'email'=>Yii::t('frontend', 'Email'),
            'password'=>Yii::t('frontend', 'Password'),
            'rememberMe'=>Yii::t('frontend', 'Remember Me'),
            'token'=>Yii::t('frontend', 'Token'),
            'form_step'=>'Form Step',
        ];
    }
    public function validateAccount($isApi=false) {
        $user = $this->getUser();
        $msg = null;
        $key = null;

        if($user->account_status == User::ACCOUNT_STATUS_EXCEED_MAX_LOGIN_ATTEMPT) {
            $msg = "Exceeded login attempt limit, this account has been suspended.";
            $this->addError('email', Yii::t('frontend', $msg));
            $key = CustomHttpException::ACCOUNT_OVER_MAX_LOGIN_ATTEMPT;
        }
        if ($user->account_status == User::ACCOUNT_STATUS_SUSPENDED) {
            $msg = "This account has been suspended";
            $this->addError('email', Yii::t('frontend', $msg));
            $key = CustomHttpException::ACCOUNT_SUSPENDED;
        }
        if ($user->email_status == User::EMAIL_STATUS_NOT_VERIFIED) {
            $msg = "Your email address must be verified before you can log in.";
            $this->addError('email', Yii::t('frontend', $msg));
            $key = CustomHttpException::EMAIL_NOT_VERIFIED;
        }        
        if ($user->status == MyCustomActiveRecord::STATUS_DISABLED) {
            $msg = "Your account is disabled.";
            $this->addError('email', Yii::t('frontend', $msg));
            $key = CustomHttpException::ACCOUNT_DISABLED;
        }
        if ($msg != null) {
            $this->addError('email', Yii::t('frontend', $msg));
            if ($isApi) {
                throw new CustomHttpException($msg, CustomHttpException::FORBIDDEN, $key);
            }
            return false;
        }

        return true;
    }
    public function validatePassword() {
        $user = $this->getUser();
        if ($user) {
            if ($user->validatePassword($this->password . $user->password_salt)) {
                $user->updateAttributes(['login_attempt' => 0]);
                return true;
            } else {
                $user->updateCounters(['login_attempt' => 1]);       
                if($user->login_attempt >= User::MAX_LOGIN_ATTEMPTS){
                    $user->updateAttributes(['status_account' => User::ACCOUNT_STATUS_EXCEED_MAX_LOGIN_ATTEMPT]);    
                    //$user->updateAttributes(['login_attempt' => 0]);                  
                }         
                $this->addError('password', Yii::t('frontend', 'Incorrect email or password.'));
                return false;
            }
        }
        $this->addError('password', Yii::t('frontend', 'Incorrect email or password.'));
        return false;
    }
    public function login() {
        if ($this->validate() && $this->validatePassword() && $this->validateAccount()) {
            if (Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 : 0)) {
                if (!Yii::$app->user->can('loginToCms')) {
                    Yii::$app->user->logout();
                    throw new ForbiddenHttpException;
                }
                return true;
            }
        }
        return false;    
    }
    //for otp logic after users has already been validated
    public function loginWithEmail($email) {
        $this->email = $email;
        $user = $this->getUser();
        if (Yii::$app->user->login($user, $this->rememberMe ? Time::SECONDS_IN_A_MINUTE : 0)) {
            return true;
        }
        return false;    
    }
    public function loginApi() {
        if ($this->validate() && $this->validateAccount(true)) {
            Yii::$app->user->login($this->getUser());
            return true;
        }
        return false;
    }

    public function getUser()
    {
        if ($this->user == null) {
            $this->user = User::find()
                //->active()
                ->andWhere(['email'=>$this->email])
                ->one();
        }
        return $this->user;
    }

    public function sendOtp(){
        
        if (self::isRequested($this->user)){
            //check for any previous record
            $this->addError('token', Yii::t('frontend', 'Please try again in 1 minute for a new OTP.'));
            return;
        }
        
        $token = random_int(100001,999999);
        self::saveToTable($this->user, $token);
        return;
    }

    private function isRequested($model){
        $m = UserToken::find()->orderBy(['created_at'=>SORT_DESC])->andWhere(['user_id'=>$model->id])->andWhere(['type'=>UserToken::TYPE_ONE_TIME_PASSWORD])->one();

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




    public function validateOtp($email) {
        $user_id = User::find()->andWhere(['email'=>$email])->one()->id;
        $m = UserToken::find()->orderBy(['created_at'=>SORT_DESC])->andWhere(['user_id'=>$user_id])->andWhere(['type'=>UserToken::TYPE_ONE_TIME_PASSWORD])->one();
        //print_r($m);
        //exit();
        if($m->token !== $this->token){
            $this->token = "";
            $this->addError('token', Yii::t('backend', 'Incorrect token.'));
            return false;
        } else if(self::isTokenExpired($m->created_at)){
            //check within 1 min
            $this->token = "";
            $this->addError('token', Yii::t('frontend', 'Token expired.'));
            return false;
        }
        
        $m->delete();
        return true;
    }

}