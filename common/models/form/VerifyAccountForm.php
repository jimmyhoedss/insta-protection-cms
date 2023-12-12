<?php
namespace common\models\form;

use common\models\User;
use common\models\SysUserToken;
use yii\base\InvalidParamException;
use yii\base\Model;
use Yii;

class VerifyAccountForm extends Model
{

    public $token;
    private $tokenModel;

    public function rules()
    {
        return [
            [['token'], 'required'],
            ['token', 'validateToken'],
            // ['token', 'string', 'max'=>10]
        ];
    }

    public function validateToken() {
        if (empty($this->token) || !is_string($this->token)) {
            $this->addError('token', Yii::t('common', 'Verify token cannot be blank.'));
            return false;
        }
        $this->tokenModel = SysUserToken::find()
            ->notExpired()
            ->byType(SysUserToken::TYPE_EMAIL_ACTIVATION)
            ->byToken($this->token)
            ->one();

        if (is_null($this->tokenModel)) {
            $this->addError('token', Yii::t('common', 'Wrong verify token.'));
            return false;
        }
        return true;
    }

    public function verifyAccount()
    {
        $user = $this->tokenModel->userData;
        // print_r($user);
        // exit();
        if ($user) {
            $user->updateAttributes(['email_status' => User::EMAIL_STATUS_VERIFIED]);  
            $this->tokenModel->delete();
        } else {
            $this->addError('token', Yii::t('common', 'No user.'));
            return false;
        }
        return true;
    }

    public function getEmail() {
        return $this->tokenModel->userData->email;
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'token'=>Yii::t('common', 'Token')
        ];
    }
}
