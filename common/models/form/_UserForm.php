<?php
namespace common\models\form;

use common\models\User;
use common\models\UserProfile;
use common\models\SysAuditTrail;
use trntv\filekit\behaviors\UploadBehavior;
use yii\base\Exception;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use Yii;

class UserForm extends Model
{
    public $avatar;
    public $avatar_path;
    public $avatar_base_url;
    public $nickname;
    public $email;
    public $password;
    public $status;
    public $notes;
    public $account_status;
    public $email_status;
    public $roles;
    private $model;
    //private $profileModel;

    /**
     * @inheritdoc
     */

    public function behaviors()
    {
        return [
            "upload" =>
            [
                'class' => UploadBehavior::className(),
                'attribute' => 'avatar',
                'pathAttribute' => 'avatar_path',
                'baseUrlAttribute' => 'avatar_base_url'
            ], 
        ];

    }

    public function rules()
    {
        return [
            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            /**/
            ['email', 'unique', 'targetClass'=> User::className(), 'filter' => function ($query) {
                if (!$this->getModel()->isNewRecord) {
                    $query->andWhere(['not', ['id'=>$this->getModel()->id]]);
                }
            }],
            

            ['password', 'required', 'on' => 'create'],
            [['password'], 'string', 'min' => 8, 'max' => 128],
            ['password', 'match', 'pattern' => '/^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])(?=\S*[\W])\S*$/', 'message' => Yii::t('common', 'Your password require at least one upper-case letter and at least one digit')]],

            [['status', 'account_status', 'email_status', 'notes'], 'string'],
            [['nickname'], 'string', 'min' => 6, 'max' => 30],
            [['notes'], 'string', 'max' => 2048],
            
            [['roles'], 'safe', 'on' => 'adminMode'],
            [['roles'], 'each',
                'rule' => ['in', 'range' => ArrayHelper::getColumn(
                    Yii::$app->authManager->getRoles(),
                    'name'
                )], 
                'on' => 'adminMode'
            ],//important for to disallow manager to change role!!
            [['avatar'], 'safe'] //important for upload!!
        
            
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'email' => Yii::t('common', 'Email'),
            'status' => Yii::t('common', 'Status'),
            'notes' => Yii::t('common', 'Notes'),
            'account_status' => Yii::t('common', 'Account Status'),
            'email_status' => Yii::t('common', 'Email Status'),
            'password' => Yii::t('common', 'Password'),
            'roles' => Yii::t('common', 'Roles'),
        ];
    }

    /**
     * @param User $model
     * @return mixed
     */
    public function setModel($model)
    {
        $this->email = $model->email;
        $this->nickname = utf8_decode($model->userProfile->nickname);
        $this->notes = $model->notes;
        $this->status = $model->status;
        $this->account_status = $model->account_status;
        $this->email_status = $model->email_status;
        $this->avatar = [];
        $this->avatar["base_url"] = $model->userProfile->avatar_base_url;
        $this->avatar["path"] = $model->userProfile->avatar_path;

        $this->model = $model;
        
        $this->roles = ArrayHelper::getColumn(
            Yii::$app->authManager->getRolesByUser($model->getId()),
            'name'
        );
        return $this->model;
    }

    /**
     * @return User
     */
    public function getModel()
    {
        if (!$this->model) {
            $this->model = new User();
        }
        return $this->model;
    }

    public function save()
    {
        if ($this->validate()) {
            $model = $this->getModel();
            $model->email = $this->email;
            $model->notes = $this->notes;
            $model->status = $this->status;
            $model->account_status = $this->account_status;
            $model->email_status = $this->email_status;

            if ($model->userProfile) {
                $model->userProfile->avatar_base_url = $this->avatar_base_url;
                $model->userProfile->avatar_path = $this->avatar_path;
                $model->userProfile->nickname = utf8_encode($this->nickname);
                if (!$model->userProfile->save(false)) {
                    print_r($model->userProfile->errors,true);
                    throw new Exception('Model UserProfile not saved');
                }
            }
            if ($this->password) {
                $model->setPassword($this->password);
            }
            if (!$model->save()) {
                throw new Exception('Model not saved');
            }

 
            $auth = Yii::$app->authManager;
            $auth->revokeAll($model->getId());

            if ($this->roles && is_array($this->roles)) {
                foreach ($this->roles as $role) {
                    $auth->assign($auth->getRole($role), $model->getId());
                }
            }
            SysAuditTrail::leaveTrail($model->id, $this);


            return !$model->hasErrors();
        }
        return null;
    }
}