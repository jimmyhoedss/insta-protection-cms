<?php
namespace common\models\form;

use common\models\UserProfile;
use yii\base\InvalidParamException;
use yii\base\Model;
use Yii;

class UploadProfileAvatarForm extends Model
{
    public $user_id; 
    public $image_file;

    public function init() {
        $this->user_id = Yii::$app->user->id;
    }

    public function rules()
    {
        return [
            [['user_id'], 'required'],
            //TODO:: Can't validate properly, fix later.
            [['image_file'], 'file', 'skipOnEmpty' => false, 'extensions' => 'png, jpg, jpeg', 'maxSize'=>1024 * 1024 * 5,  'maxFiles' => 1],
            [['user_id'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'user_id' => 'User ID',
            'image_file' => 'Image File',
        ];
    }
}
