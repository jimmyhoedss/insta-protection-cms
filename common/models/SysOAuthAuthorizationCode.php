<?php

namespace common\models;

use Yii;
use common\components\Utility;
use common\models\User;

class SysOAuthAuthorizationCode extends \yii\db\ActiveRecord
{

    public static function tableName()  {
        return 'sys_oauth_authorization_code';
    }
    public function rules() {
        return [
            [['code', 'expire_at', 'user_id', 'created_at', 'updated_at'], 'required'],
            [['expire_at', 'user_id', 'created_at', 'updated_at'], 'integer'],
            [['code'], 'string', 'max' => 150],
            [['app_id'], 'string', 'max' => 200],
        ];
    }

    public function attributeLabels() {
        return [
            'id' => 'ID',
            'code' => 'Code',
            'expire_at' => 'Expire At',
            'user_id' => 'User ID',
            'app_id' => 'App ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getUser() {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public static function isValid($code) {
        $model = self::findOne(['code' => $code]);
        if($model && $model->expire_at>time()) {
            return $model;
        } else {
            return null;            
        }
    }

    public static function deleteAllUserCodes($user) {
        SELF::deleteAll('user_id = '.$user->id);
    }

    public static function makeModel($user) {
        $m = new SELF;
        $m->code = Utility::randomToken();
        $m->expire_at = time() + (60 * 5);
        $m->user_id = $user->id;
        $m->app_id = null;
        $m->created_at = time();
        $m->updated_at = time();

        return $m;
    }

    


}
