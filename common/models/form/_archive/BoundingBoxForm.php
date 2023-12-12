<?php
namespace common\models\form;

use common\models\User;
use common\models\UserToken;
use yii\base\InvalidParamException;
use yii\base\Model;
use Yii;

class BoundingBoxForm extends Model
{
    public $x0;
    public $y0;
    public $x1;
    public $y1;

    public function rules()
    {
        return [
            [['x0','y0','x1','y1'], 'required'],
            [['x0','y0','x1','y1'], 'number'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'x0'=>'x0',
            'y0'=>'y0',
            'x1'=>'x1',
            'y1'=>'y1'
        ];
    }
}
