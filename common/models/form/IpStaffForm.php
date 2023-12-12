<?php
namespace common\models\form;

use common\models\User;
use yii\base\InvalidParamException;
use yii\base\Model;
use Yii;
use yii\web\JsExpression;
use common\components\MyCustomBadRequestException;

class IpStaffForm extends Model
{
    const SCENARIO_ASSIGN_IP_STAFF = "scenario_assign_ip_staff";
    const SCENARIO_ASSIGN_IP_STAFF_PERMISSION = "scenario_assign_ip_staff_permission";

    public $permission_arr;
    public $role_arr;
    public $notes;
    public $user_id;
    public $email_admin;
    public $password;

    public function rules()
    {
        return [
            // [['dealer_company_id'], 'required', 'on' => SELF::SCENARIO_FORCE_LOGOUT_COMPANY],  
            // [['user_id'], 'required', 'on' => SELF::SCENARIO_FORCE_LOGOUT_USER],            
            [['user_id', 'email_admin', 'password'], 'required' , 'on' => SELF::SCENARIO_ASSIGN_IP_STAFF],
            [['permission_arr'], 'safe'],      
            ['password', 'match', 'pattern' => '/^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])(?=\S*[\W])\S*$/', 'message' => 'Password must contain 8 or more characters with at least 1 UPPER case character, 1 lower case character, 1 numeric character and 1 special character.'],
            [['email_admin'], 'email'],      
            [['email_admin', 'password'], 'string'],      
            [['role_arr'], 'required'],      
        ];
    }
    
 
    public function attributeLabels()
    {
        return [
            'role_arr'=>Yii::t('common', 'Privilege'),
            'permission_arr'=>Yii::t('common', 'Access Rights'),
            'email_admin'=>Yii::t('common', 'Email Admin'),
            'password'=>Yii::t('common', 'Password'),
        ];
    }
}
