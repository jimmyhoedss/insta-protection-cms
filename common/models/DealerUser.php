<?php

namespace common\models;

use Yii;

use common\models\DealerCompany;
use common\models\User;
use common\components\MyCustomActiveRecord;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;


class DealerUser extends MyCustomActiveRecord
{
    public $roles;
    const SCENARIO_CMS = "cms";
    const SCENARIO_API = "api";

    public static function tableName() {
        return 'dealer_user';
    }

    public function rules() {
        return [
            [['dealer_company_id', 'user_id', 'roles'], 'required', 'on'=>self::SCENARIO_CMS],
            [['dealer_company_id', 'user_id'], 'required', 'on'=>self::SCENARIO_API],
            [['dealer_company_id', 'user_id', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['notes'], 'string'],
        ];
    }

    public function attributeLabels() {
        return [
            // 'id' => Yii::t('common', 'ID'),
            'dealer_company_id' => Yii::t('common', 'Dealer Company ID'),
            'user_id' => Yii::t('common', 'User ID'),
            'roles' => Yii::t('common', 'Position'),
            'notes' => Yii::t('common', 'Notes'),
            'created_at' => Yii::t('common', 'Created At'),
            'created_by' => Yii::t('common', 'Created By'),
            'updated_at' => Yii::t('common', 'Updated At'),
            'updated_by' => Yii::t('common', 'Updated By'),
        ];
    }

    public static function getDealerFromUserId($user) {
        $model = SELF::find()->where(['user_id'=>$user->id])->active()->one();

        if ($model) {
            $dealer = DealerCompany::find()->where(['id'=>$model->dealer_company_id])->one();
            return $dealer;
        }
        return null;        
    }
    //Loynote:: why no name as getCompany
    public function getDealer() {
        return $this->hasOne(DealerCompany::className(), ['id' => 'dealer_company_id']);
    }

    public function getUserProfile() {
        return $this->hasOne(UserProfile::className(), ['user_id' => 'user_id']);
    }

    public function getUser() {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public static function getAllDealerManager($dealer_company_id) {
        $dm = DealerUser::find()->andWhere(['dealer_company_id' => $dealer_company_id, 'status' => MyCustomActiveRecord::STATUS_ENABLED])->join('LEFT JOIN','rbac_auth_assignment','rbac_auth_assignment.user_id = dealer_user.user_id')->andFilterWhere(['rbac_auth_assignment.item_name' => User::ROLE_DEALER_MANAGER])->all();

        return $dm;
    }

    public static function getRoleArray($user_id) {
        $du_auth = Yii::$app->authManager;
        $item = $du_auth->getRolesByUser($user_id);
        $role_names = array_values($item);
        $arr = [];
        for($i = 0; $i< count($role_names); $i++){
            array_push($arr, $role_names[$i]->name);
        }
        return $arr;
    }

    public static function revokeDealerRolesByUserId($user_id, $authManager) {
        // $du_auth = Yii::$app->authManager;
        $item = $authManager->getRolesByUser($user_id);

        for($i=0; $i<count($item); $i++) {
            $role = array_values($item)[$i];
            // exit();
            if($role->name == User::ROLE_DEALER_MANAGER || $role->name == User::ROLE_DEALER_ASSOCIATE){
                $authManager->revoke($role,$user_id);
            }
        }

        return true ;
    }
    
    public static function find()
    {
        return new \common\models\query\DealerUserQuery(get_called_class());
    }

   /* public function toObjectArray($models) {
        $d = [];
        foreach ($models as $m) {
            $o = $m->toObject();
            $d[] = $o;
        }
        return $d;
    }*/

    public function toObject() {
        $m = $this;
        $u = $m->user;
        $up = $m->userProfile;
        
        $o = (object) [];
        $o->dealer_company_id = $m->dealer_company_id;
        $o->company_name = $m->dealer->business_name;


        $o = (object) array_merge((array) $o, (array) $u->userDetails);
        return $o;
    }

    public function dealerUserOject() {
        $m = $this;
        $u = $m->user;
        $up = $m->userProfile;
        
        $o = (object) [];
        $o->company_name = $m->dealer->business_name;
        $o->avatar_url = $m->userProfile->avatar;       
        // $o->avatar_path =  $m->userProfile->avatar_path;
        return $o;
    }

    public static function makeModel($dealer, $dealer_user_id) {
        $m = new SELF();
        $m->dealer_company_id = $dealer->id;
        $m->user_id = $dealer_user_id;
        return $m;
    }    


    public static function getUserNotInAnyCompanyConcatWithUserName() {
        $dealer_user_arr = DealerUser::find()->active()->asArray()->all();
        $user_id_arr = array_column($dealer_user_arr, 'user_id');
        $users = User::find()->active()->where(['not in', 'id', $user_id_arr])->andWhere(['region_id' => Yii::$app->session->get('region_id')])->all();
        //concat with user name 
        $concatUser = ArrayHelper::map($users, 'id', function($model) {
            $seperator = (empty($model->userProfile->first_name) && empty($model->userProfile->last_name)) ? "" : " - ";
            return $model->mobile_number_full.$seperator.$model->userProfile->first_name.$model->userProfile->last_name;
        });
        return $concatUser;
    }  

    //*********** html layout ***********


   
}
