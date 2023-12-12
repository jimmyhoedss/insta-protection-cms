<?php

namespace common\models;

use Yii;
use common\components\Utility;

class SysOAuthAccessToken extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'sys_oauth_access_token';
    }

    public function init() {

    }
    public function rules()
    {
        return [
            [['token', 'expire_at', 'auth_code', 'user_id', 'created_at', 'updated_at'], 'required'],
            [['expire_at', 'user_id', 'created_at', 'updated_at'], 'integer'],
            [['token'], 'string', 'max' => 300],
            [['auth_code', 'app_id'], 'string', 'max' => 200],
        ];
    }
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'token' => 'Token',
            'expire_at' => 'Expire At',
            'auth_code' => 'Auth Code',
            'user_id' => 'User ID',
            'app_id' => 'App ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public static function deleteAllUserToken($user) {
        SELF::deleteAll('user_id = '.$user->id);
    }

    public static function makeModel($authorization_code, $user)
    {
        $m = new SELF();
        $m->token = Utility::randomToken();         
        $m->auth_code = $authorization_code;
        $m->expire_at = time() + (60 * 60 * 24 * 60); // 60 days
        $m->user_id = $user->id;
        $m->created_at = time();
        $m->updated_at = time();
        return $m;
    }

    
}
