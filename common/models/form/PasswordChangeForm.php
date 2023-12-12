<?php
namespace common\models\form;

use common\models\User;
use yii\base\InvalidParamException;
use yii\base\Model;
use Yii;
use yii\web\JsExpression;
use common\components\MyCustomBadRequestException;

/**
 * Password reset form
 */
class PasswordChangeForm extends Model
{
    /**
     * @var
     */
    public $password;
    public $password_confirm;
    public $current_password;
    public $hash;

    /**
     * Creates a form model given a token.
     *
     * @param  string                          $token
     * @param  array                           $config name-value pairs that will be used to initialize the object properties
     * @throws \yii\base\InvalidParamException if token is empty or not valid
     */

    private $currentPasswordModel;

    /*
    public function __construct($token, $config = [])
    {
        if (empty($token) || !is_string($token)) {
            throw new InvalidParamException('Password reset token cannot be blank.');
        }
        $this->token = UserToken::find()
            ->notExpired()
            ->byType(UserToken::TYPE_PASSWORD_RESET)
            ->byToken($token)
            ->one();

        if (!$this->token) {
            throw new InvalidParamException('Wrong password reset token.');
        }
        parent::__construct($config);
    }
    */

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['current_password','password', 'password_confirm'], 'required'],            
            ['current_password', 'validateCurrentPassword'],
            [['password'], 'string', 'min' => 8, 'max' => 128],
            ['password', 'match', 'pattern' => '/^(?=.*[0-9])(?=.*[A-Z])([a-zA-Z0-9!@#$%^&*()]+)$/', 'message' => Yii::t('common', 'Your password require at least one upper-case letter and at least one digit')],
            ['password_confirm', 'compare', 'compareAttribute' => 'password', 'skipOnEmpty' => false],
            /*
            [
                'password_confirm',
                'required',
                'when' => function($model) {
                    return !empty($model->password);
                },
                'whenClient' => new JsExpression("function (attribute, value) {
                    return $('#caccountform-password').val().length > 0;
                }")
            ],
            */       
        ];
    }
    
    public function validateCurrentPassword() {
        if (empty($this->current_password) || !is_string($this->current_password)) {
            $this->addError('current_password', Yii::t('common', 'Password reset token cannot be blank.'));
            return false;
        }        

        $user_id = Yii::$app->user->id;

        $this->currentPasswordModel = User::find('password_hash')
            ->andWhere(['id'=>$user_id])
            ->one();

        $this->hash = $this->currentPasswordModel->password_hash;

        if (password_verify($this->current_password, $this->hash)) {
            return true;
        } else {
            throw new MyCustomBadRequestException(MyCustomBadRequestException::WRONG_CURRENT_PASSWORD, Yii::t('common',"Invalid current password, please try again"));
            return false;
        }        
    }
    
    public function changePassword()
    {
        $user = Yii::$app->user->identity;
        $user->password = $this->password;
        if($user && $user->save()) {
            $user->updateAttributes(['password' => $this->password]);         
        } else {
            $this->addError('password', Yii::t('common', 'No user.'));
            return false;
        }               

        return true;
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'password'=>Yii::t('frontend', 'Password')
        ];
    }
}
