<?php
namespace common\models\form;

use common\models\UserPostLocation;
use yii\base\InvalidParamException;
use yii\base\Model;
use Yii;
use common\components\WithinC2cBorderValidator;

class UploadPostLocationForm extends Model
{
    public $user_id;
    public $location;
    public $description;    
    public $image_file;
    public $latitude;
    public $longitude;

    public function init() {
        $this->user_id = Yii::$app->user->id;
        $this->location = "-";
    }

    public function rules()
    {
        return [
            [['user_id', 'latitude', 'longitude','image_file'], 'required'],
            //TODO:: Can't validate properly, fix later.
            [['image_file'], 'image', 'skipOnEmpty' => false, 'extensions' => 'png, jpg, jpeg', 'maxSize'=>1024 * 1024 * 5, 'minWidth' => 16,'maxWidth' => 1600, 'minHeight' => 16, 'maxHeight' => 1600, 'maxFiles' => 1],
            [['user_id'], 'integer'],
            [['latitude', 'longitude'], 'number'],
            [['location', 'description'], 'string', 'max' => 1024],
            [['latitude', 'longitude'], WithinC2cBorderValidator::className()],
        ];
    }

    public function attributeLabels()
    {
        return [
            'user_id' => 'User ID',
            'location' => 'Location',
            'description' => 'Description',
            'image_file' => 'Image File',
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
        ];
    }
}
