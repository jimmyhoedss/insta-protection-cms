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
class CompanyPlanForm extends Model
{
    //array of plans available for company to sell, reference to instap_plan_dealer_company table
    public $plan_id_arr= [];

    public function rules()
    {
        return [          
            [['plan_id_arr'], 'safe'],            
        ];
    }
    
 
    public function attributeLabels()
    {
        return [
            'plan_id_arr'=>Yii::t('common', 'Plan')
        ];
    }
}
