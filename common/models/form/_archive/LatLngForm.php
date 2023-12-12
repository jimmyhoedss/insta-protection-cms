<?php
namespace common\models\form;

use common\models\User;
use common\models\UserToken;
use yii\base\InvalidParamException;
use yii\base\Model;
use Yii;

class LatLngForm extends Model
{
    public $latitude;
    public $longitude;
    public $radius;

    public function rules()
    {
        return [
            [['longitude', 'latitude'], 'required'],
            [['longitude', 'latitude'], 'number'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'latitude'=>'latitude',
            'longitude'=>'longitude'
        ];
    }
}