<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use common\behaviors\MyAuditTrailBehavior;
use api\components\CustomHttpException;
use common\models\UserPlanAction;

class UserPlanActionDocument extends \yii\db\ActiveRecord
{
    const TYPE_REGISTRATION = "photo_registration";
    const TYPE_REGISTRATION_RESUBMIT = "photo_registration_resubmit";
    const TYPE_DEVICE_ASSESSMENT = "photo_device_assessment";
    const TYPE_PHOTO_INTERNAL = "photo_internal"; //Oh: for admin upload photo for internal review

    public static function tableName() {
        return 'user_plan_action_document';
    }

    public function rules() {
        return [
            [['plan_action_id', 'type'], 'required'],
            [['plan_action_id', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['status', 'type'], 'string'],
            ['type', 'in', 'range' => array_keys(self::allDocumentType())],
            [['thumbnail_base_url', 'thumbnail_path'], 'string', 'max' => 1024],
        ];
    }
    public function behaviors() {
        return [
            "timestamp" => TimestampBehavior::className(),
            "blame" => BlameableBehavior::className(),
            "auditTrail" => MyAuditTrailBehavior::className(),
        ];
    }

    public function attributeLabels() {
        return [
            'id' => Yii::t('common', 'ID'),
            'plan_action_id' => Yii::t('common', 'Plan Action ID'),
            'thumbnail_base_url' => Yii::t('common', 'Thumbnail Base Url'),
            'thumbnail_path' => Yii::t('common', 'Thumbnail Path'),
            'status' => Yii::t('common', 'Status'),
            'created_at' => Yii::t('common', 'Created At'),
            'created_by' => Yii::t('common', 'Created By'),
            'updated_at' => Yii::t('common', 'Updated At'),
            'updated_by' => Yii::t('common', 'Updated By'),
        ];
    }

    public static function allDocumentType() {
        return [
            self::TYPE_REGISTRATION => Yii::t('common','registration photo'),
            self::TYPE_REGISTRATION_RESUBMIT => Yii::t('common','registration photo resubmit'),
            self::TYPE_DEVICE_ASSESSMENT => Yii::t('common','device assessment photo'),
            self::TYPE_PHOTO_INTERNAL => Yii::t('common','internal photo')

        ];
    }

    public static function mapActionToDocumentType() {
        return [
            UserPlanAction::ACTION_UPLOAD_PHOTO => self::TYPE_REGISTRATION,
            UserPlanAction::ACTION_PHYSICAL_ASSESSMENT=> self::TYPE_DEVICE_ASSESSMENT,
            UserPlanAction::ACTION_UPLOAD_PHOTO_ADMIN=> self::TYPE_PHOTO_INTERNAL
        ];
    }

    public static function find() {
        return new \common\models\query\UserPlanActionDocumentQuery(get_called_class());
    }

    public static function makeModel($planAction, $item, $type) {
        $m = new SELF();
        $m->plan_action_id = $planAction->id;
        $m->type = $type;
        $m->thumbnail_base_url = $item['base_url'];
        $m->thumbnail_path = $item['path'];
        return $m;
    }    

    public static function uploadDocument($modelAction, $arr, $type) {
         //upload image
         //loop & save all photos to model
        if(!empty($arr)) {
            for ($i=0; $i < count($arr); $i++) {
                $item = $arr[$i];
                $p = self::makeModel($modelAction, $item, $type);
                if(!$p->save()) {
                    throw CustomHttpException::internalServerError(Yii::t('common',"Cannot update plan photo."));
                }                        
            }             
        }
    }

}
