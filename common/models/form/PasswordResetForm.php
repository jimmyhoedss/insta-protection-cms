<?php
namespace common\models\form;

use common\models\User;
use common\models\UserToken;
use yii\base\InvalidParamException;
use yii\base\Model;
use Yii;
use yii\web\JsExpression;

/**
 * Password reset form
 */
class PasswordResetForm extends Model
{
    public $password;
    public $password_confirm;
    public $token;

    /**
     * @var \common\models\UserToken
     */
    private $tokenModel;

    public function rules()
    {
        return [
            [['token', 'password', 'password_confirm'], 'required'],
            ['token', 'validateToken'],
            [['password'], 'string', 'min' => 8, 'max' => 128],
            ['password', 'match', 'pattern' => '/^(?=.*[0-9])(?=.*[A-Z])([a-zA-Z0-9!@#$%^&*()]+)$/', 'message' => Yii::t('common', 'Your password require at least one upper-case letter and at least one digit')],
            ['password_confirm', 'compare', 'compareAttribute' => 'password', 'skipOnEmpty' => false],
        ];
    }

    public function validateToken() {
        if (empty($this->token) || !is_string($this->token)) {
            $this->addError('token', Yii::t('common', 'Password reset token cannot be blank.'));
            return false;
        }
        
        $this->tokenModel = UserToken::find()
            //->notExpired()
            ->byType(UserToken::TYPE_PASSWORD_RESET)
            ->byToken($this->token)
            ->one();
        
        if (!$this->tokenModel) {
            $this->addError('token', Yii::t('common', 'Wrong password reset token.'));
            return false;
        } else {
            if($this->isTokenExpired()){
                $this->addError('token', Yii::t('common', 'Reset token has expired.'));
                $this->tokenModel->delete();
                return false;
            }    
        }        
        return true;
    }

    private function isTokenExpired() {
        $expiredAt = $this->tokenModel->expire_at;
        $date = date_create();
        $timeDifference = (date_timestamp_get($date) - $expiredAt); 
        //print_r($timeDifference);
        //exit();

        if ($timeDifference > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function resetPassword() {
        $user = $this->tokenModel->user;
        $user->password = $this->password;
        if($user && $user->save()) {
            $user->updateAttributes(['password' => $this->password]);
            $user->updateAttributes(['account_status' => $user::ACCOUNT_STATUS_NORMAL]);
            $user->updateAttributes(['login_attempt' => 0]);
            $this->tokenModel->delete();
        } else {
            $this->addError('password', Yii::t('common', 'No user.'));
            return false;
        }

        return true;
    }

    public function getEmail() {
        return $this->tokenModel->user->email;
    }


    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'password'=>Yii::t('frontend', 'Password'),
            'password_confirm'=>Yii::t('frontend', 'Confirm Password')
        ];
    }
}
