<?php
namespace common\models\form;

use Yii;
use yii\base\Model;
use common\models\User;
use common\models\UserProfile;

class RegistrationForm extends Model
{
    public $title;
    public $first_name;
    public $last_name;
    public $gender;
    public $country;
    public $password;
    public $password_confirm;
    public $email;

    public function rules()
    {
        return [
            [['password', 'password_confirm', 'email', 'title', 'first_name', 'last_name', 'gender'], 'required'],
            [['email', 'title', 'first_name', 'last_name'], 'string'],
            [['gender'], 'integer'],
            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'email'],
            ['email', 'unique',
                'targetClass'=> '\common\models\User',
                'message' => Yii::t('frontend', 'This email address has already been taken.')
            ],
            [['password'], 'string', 'min' => 8, 'max' => 128],
            ['password', 'match', 'pattern' => '/^(?=.*[0-9])(?=.*[A-Z])([a-zA-Z0-9!@#$%^&*()]+)$/', 'message' => 'Your password require at least one upper-case letter and at least one digit'],
            ['password_confirm', 'compare', 'compareAttribute' => 'password', 'skipOnEmpty' => false],
            
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'email'=>Yii::t('frontend', 'E-mail'),
            'password'=>Yii::t('frontend', 'Password'),
        ];
    }

    public function register()
    {
        if ($this->validate()) {

            $success = true;
            $transaction = Yii::$app->db->beginTransaction();

            $user = new User();
            $user->email = $this->email;
            $user->generateSalt();
            $user->setPassword($this->password);

            if($user->save()) {               
                $userProfile = new UserProfile();
                $userProfile->user_id = $user->id;
                $userProfile->title = $this->title; 
                $userProfile->first_name = $this->first_name;
                $userProfile->last_name = $this->last_name;
                $userProfile->gender = $this->gender;

                if (!$userProfile->save()) {
                    $success = false;
                };
                
            } else {
                $success = false;
            }           

            if ($success) {
                // $user->addToTimeline();
                // $user->createAndSendActiviationTokenEmail();
                $transaction->commit();

                return $user;
            } else {
                $transaction->rollback();
                return null;
            }
        }

        return null;
    }

}
