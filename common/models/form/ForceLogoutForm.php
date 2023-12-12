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
class ForceLogoutForm extends Model
{
    const SCENARIO_FORCE_LOGOUT_COMPANY = "force_logout_company";
    const SCENARIO_FORCE_LOGOUT_USER = "force_logout_user";
    public $dealer_company_id;
    public $user_id;

    public function rules()
    {
        return [
            // [['dealer_company_id'], 'required', 'on' => SELF::SCENARIO_FORCE_LOGOUT_COMPANY],  
            // [['user_id'], 'required', 'on' => SELF::SCENARIO_FORCE_LOGOUT_USER],            
            [['user_id', 'dealer_company_id'], 'required'],            
        ];
    }
    
 
    public function attributeLabels()
    {
        return [
            'dealer_company_id'=>Yii::t('frontend', 'Dealer Company id')
        ];
    }
}
