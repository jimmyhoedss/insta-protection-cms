<?php
namespace common\models\form;

use common\models\User;
use common\models\UserToken;
use yii\base\InvalidParamException;
use yii\base\Model;
use Yii;

class ListPostLocationClusterForm extends Model
{
    public $latitude;
    public $longitude;
    public $result;
    const RADIUS = 1.05; //1050 meters

    public function rules()
    {
        return [
            [['longitude', 'latitude', 'result'], 'required'],
            [['longitude', 'latitude', 'result'], 'number'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'latitude'=>'latitude',
            'longitude'=>'longitude',
            'result'=>'result'
        ];
    }
}