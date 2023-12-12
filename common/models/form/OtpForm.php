<?php
namespace common\models\form;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use common\components\MyCustomModel;
use common\models\User;
use common\models\UserProfile;
use common\components\Utility;
use common\models\form\RegistrationForm;
use common\models\SysUserToken;
use api\components\CustomHttpException;

class OtpForm extends MyCustomModel
{
    const METHOD_SMS = 0;
    const METHOD_EMAIL = 1;
    const CAPTCHA = "captcha";
    public $user;
    public $region_id = "SG";
    public $mobile_calling_code = "65";
    public $mobile_number;
    public $mobile_number_full;
    public $flag_method = 0;
    public $reCaptcha;

    public function rules()
    {
        return ArrayHelper::merge([
            ['flag_method', 'in', 'range' => [SELF::METHOD_SMS, SELF::METHOD_EMAIL]],
            ['reCaptcha', 'captcha','captchaAction'=> 'site/captcha','message'=>"Enter the characters shown in the image to proceed.", 'on' => SELF::CAPTCHA],
        ], RegistrationForm::registrationRules());
    }

     public function attributeLabels() {
        return RegistrationForm::registrationAttributeLabels();
    }
    //for backend
    public function getAdminUser()
    {
        $user = User::findByFullMobileNumber($this->mobile_number_full);
        if ($user && Yii::$app->authManager->checkAccess($user->id, User::PERMISSION_LOGIN_TO_CMS)) {
            return $user;    
        } else {
            $this->addError('mobile_number', Yii::t('common', 'No such user.'));
        }
    }

    //for dashboard
    public function getUser()
    {
        $user = User::findByFullMobileNumber($this->mobile_number_full);
        if ($user && $user->can(User::ROLE_USER)) {
            return $user;    
        } else {
            $this->addError('mobile_number', Yii::t('common', 'No such user: ' . $this->mobile_number_full));
        }
    }
    
    public function getOrRegisterUser()
    {
        $user = User::findByFullMobileNumber($this->mobile_number_full);
        if ($user == null) {
            //new user
            $form = new RegistrationForm();
            $form->attributes = $this->attributes;
            $user = $form->register();
            if ($form->hasErrors()) {
                $this->addError('mobile_number', Yii::t('common', "Registration error."));
            }
        }
        return $user;
    }

    public function sendSms($user, $type)
    {
        //$type = SysUserToken::TYPE_ONE_TIME_PASSWORD_API;
        if ($user) {
            $token = SysUserToken::getExisitingToken($user, $type);
            if ($token && $token->getCooldown() > 270 && $token->getCooldown() < 300) { // exist and within 30 seconds
                $this->addError('mobile_number_full', Yii::t('common', 'Please try again in {token} seconds for a new token', ['token' => -(270 - $token->getCooldown())]), CustomHttpException::KEY_WAIT_FOR_COOLDOWN);
                $this->addError('mobile_number', Yii::t('common', 'Please try again in {token} seconds for a new token', ['token' => -(270 - $token->getCooldown())]), CustomHttpException::KEY_WAIT_FOR_COOLDOWN);
                return null;
            } 

            SysUserToken::deleteAllUserToken($user, $type);
            $newToken = SysUserToken::makeModel($user, $type);
            if ($newToken->save()) {
                if($this->flag_method == SELF::METHOD_EMAIL){
                    // check if have email
                    if($user->email_status == User::EMAIL_STATUS_NOT_REGISTERED){
                        $this->addError('mobile_number', Yii::t('common', "No Email registered. Please use SMS mode instead, or register an Email in the profile page in the InstaProtection Mobile APP."));
                        return null;
                    } else if($user->email_status == User::EMAIL_STATUS_NOT_VERIFIED) {
                        $this->addError('mobile_number', Yii::t('common', "Email not verified. Please use SMS mode instead, or verify your Email first."));
                        return null;
                    } else {
                        $newToken->sendEmail();
                    }
                } else {
                    $newToken->sendSms();
                }
                return $newToken;
            } else {
                throw CustomHttpException::internalServerError(Yii::t('common',"Can't save token."));
            }
            
        } else {
            $this->addError('mobile_number', Yii::t('common', "No user."));
        }
        return null;
    }


}
