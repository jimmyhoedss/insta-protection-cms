<?php
namespace common\models\form;

use yii\base\InvalidParamException;
use yii\base\Model;
use Yii;

class UploadForm extends Model
{
    public $user_id;
    public $description;    
    public $image_file = [];
##

    public function init() {
        $this->user_id = Yii::$app->user->id;
    }

    public function rules()
    {

        return [
            //[['user_id',], 'required'],
            //TODO:: Can't validate properly, fix later.
            //[['image_file'], 'image', 'skipOnEmpty' => false, 'extensions' => 'png, jpg, jpeg', 'maxSize'=>1024 * 1024 * 5, 'minWidth' => 16,'maxWidth' => 1600, 'minHeight' => 16, 'maxHeight' => 1600, 'maxFiles' => 1], // loy

            //skipOnEmpty=true, maxFile=5
            // [['image_file'], 'each', 'rule' => ['image', 'skipOnEmpty' => false, 'extensions' => 'png, jpg, jpeg', 'maxSize'=>1024 * 1024 * 15, 'maxFiles' => 3]], // terry
            [['image_file'], 'image', 'skipOnEmpty' => false, 'extensions' => 'png, jpg, jpeg', 'maxSize'=>1024 * 1024 * 25, 'maxFiles' => 5], // eddie
            [['user_id'], 'integer'],
            [[ 'description'], 'string', 'max' => 1024],
        ];
    }

    public function attributeLabels()
    {
        return [
            'user_id' => 'User ID',
            'description' => 'Description',
            'image_file' => 'Image File',
        ];
    }
}