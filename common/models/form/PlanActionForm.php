<?php
namespace common\models\form;

use yii\base\Model;
use Yii;
use yii\web\JsExpression;
use trntv\filekit\behaviors\UploadBehavior;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use common\models\UserPlanAction;

class PlanActionForm extends Model
{
    const PHOTO_REG = "photo_registration";
    const PHOTO_ASSESS = "photo_assessment";

    public $photo_registration;
    public $photo_assessment;

    public function rules()
    {
        return [            
            [['photo_registration', 'photo_assessment'], 'safe'],      
            ['photo_registration', 'required', 'on' => self::PHOTO_REG],
            ['photo_assessment', 'required', 'on' => self::PHOTO_ASSESS],
            // ['registration_photo', 'required', 'when' => function() {
            //     return ($this->type === self::REGISTRATION);
            // }]
        ];
    }
    
    public function attributeLabels()
    {
        return [
            'photo_registration' =>Yii::t('common', 'Photo Registration'),
            'photo_assessment' =>Yii::t('common', 'Photo Assessment'),
        ];
    }
}
