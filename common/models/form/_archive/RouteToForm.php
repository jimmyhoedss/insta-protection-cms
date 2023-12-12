<?php
namespace common\models\form;

use common\models\User;
use common\models\UserToken;
use yii\base\InvalidParamException;
use yii\base\Model;
use Yii;

//Route to one map
//https://www.onemap.sg/main/v2/journeyplanner?start=1.31951800264429,103.842154838338&dest=1.27398639988206,103.801264139551

class RouteToForm extends Model
{
    public $startX;
    public $startY;
    public $endX;
    public $endY;

    public function rules()
    {
        return [
            [['startX','startY','endX','endY'], 'required'],
            [['startX','startY','endX','endY'], 'number'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'startX'=>'startX',
            'startY'=>'startY',
            'endX'=>'endX',
            'endY'=>'endY'
        ];
    }
}
