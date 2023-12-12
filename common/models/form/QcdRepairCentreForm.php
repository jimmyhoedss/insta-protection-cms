<?php
namespace common\models\form;

use common\models\User;
use yii\base\InvalidParamException;
use yii\base\Model;
use Yii;
use yii\web\JsExpression;
use common\components\MyCustomBadRequestException;

class QcdRepairCentreForm extends Model
{

    public $brand_id_arr;

    public function rules()
    {
        return [
            // [['dealer_company_id'], 'required', 'on' => SELF::SCENARIO_FORCE_LOGOUT_COMPANY],  
            // [['user_id'], 'required', 'on' => SELF::SCENARIO_FORCE_LOGOUT_USER],            
            [['brand_id_arr'], 'safe'],      
        ];
    }
    
 
    public function attributeLabels()
    {
        return [
            'brand_id_arr'=>Yii::t('common', 'Brands'),
        ];
    }
}
