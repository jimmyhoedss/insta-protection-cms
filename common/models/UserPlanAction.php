<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use common\behaviors\MyAuditTrailBehavior;
use common\components\MyCustomActiveRecord;
use common\components\Utility;
use common\models\InstapPlanPool;
use common\models\UserPlanActionDocument;

class UserPlanAction extends MyCustomActiveRecord
{
    const ACTION_REGISTRATION = "registration";
    const ACTION_PHYSICAL_ASSESSMENT = "physical_assessment";
    const ACTION_UPLOAD_PHOTO = "upload_photo";
    const ACTION_UPLOAD_PHOTO_ADMIN = "upload_photo_admin";
    const ACTION_REQUIRE_CLARIFICATION = "require_clarification";
    const ACTION_REGISTRATION_RESUBMIT = "registration_resubmit";
    const ACTION_APPROVE = "approve";
    const ACTION_CANCEL = "cancel";
    const ACTION_REJECT = "reject";

    public static function tableName() {
        return 'user_plan_action';
    }

    public function rules() {
        return [
            [['plan_pool_id'], 'required'],
            [['plan_pool_id'], 'integer'],
            [['notes_user', 'notes', 'action_status', 'status'], 'string'],
            [['notes_user'], 'string', 'max' => 256],
            ['action_status', 'in', 'range' => array_keys(self::allActionStatus())],
            ['notes_user', 'required', 'when' => function($model) {
                $keys = array_keys(self::requireNotesActionStatus());
                return in_array($model->action_status, $keys);
            }],
        ];
    }
    
    public function behaviors() {
        return [
            "timestamp" => TimestampBehavior::className(),
            "blame" => BlameableBehavior::className(),
            //"auditTrail" => MyAuditTrailBehavior::className(),
        ];
    }

    public function attributeLabels() {
        return [
            'id' => Yii::t('common', 'ID'),
            'plan_pool_id' => Yii::t('common', 'Plan Pool ID'),
            'notes_user' => Yii::t('common', 'Notes (User)'),
            'notes' => Yii::t('common', 'Notes (Internal)'),
            'action_status' => Yii::t('common', 'Action Status'),
            'status' => Yii::t('common', 'Status'),
            'created_at' => Yii::t('common', 'Created At'),
            'created_by' => Yii::t('common', 'Created By'),
            'updated_at' => Yii::t('common', 'Updated At'),
            'updated_by' => Yii::t('common', 'Updated By'),
        ];
    }
    public static function allActionStatus() {
        return [
            self::ACTION_REGISTRATION => Yii::t('common','Registration'),
            self::ACTION_PHYSICAL_ASSESSMENT => Yii::t('common','Physical Assessment'),
            self::ACTION_UPLOAD_PHOTO => Yii::t('common','Upload Photo'),
            self::ACTION_UPLOAD_PHOTO_ADMIN => Yii::t('common','Upload Photo (Internal)'),
            self::ACTION_REQUIRE_CLARIFICATION => Yii::t('common','Require Clarification'),
            self::ACTION_REGISTRATION_RESUBMIT => Yii::t('common','Registration Resubmit'),
            self::ACTION_APPROVE => Yii::t('common','Approve'),
            self::ACTION_CANCEL => Yii::t('common','Cancel'),
            self::ACTION_REJECT => Yii::t('common','Reject'),
        ];
    }
    public static function processPlanActionStatus()
    {
        return [
            self::ACTION_APPROVE => self::allActionStatus()[self::ACTION_APPROVE],
            self::ACTION_REQUIRE_CLARIFICATION => self::allActionStatus()[self::ACTION_REQUIRE_CLARIFICATION],
            self::ACTION_CANCEL => self::allActionStatus()[self::ACTION_CANCEL],
            self::ACTION_REJECT => self::allActionStatus()[self::ACTION_REJECT],
            self::ACTION_UPLOAD_PHOTO => self::allActionStatus()[self::ACTION_UPLOAD_PHOTO],
            self::ACTION_UPLOAD_PHOTO_ADMIN => self::allActionStatus()[self::ACTION_UPLOAD_PHOTO_ADMIN],
            self::ACTION_PHYSICAL_ASSESSMENT => self::allActionStatus()[self::ACTION_PHYSICAL_ASSESSMENT],
        ];
    }
    public static function requireNotesActionStatus() {
        return [
            self::ACTION_REQUIRE_CLARIFICATION => self::allActionStatus()[self::ACTION_REQUIRE_CLARIFICATION],
            self::ACTION_CANCEL => self::allActionStatus()[self::ACTION_CANCEL],
            self::ACTION_REJECT => self::allActionStatus()[self::ACTION_REJECT],
        ];
    }


    public static function find() {
        return new \common\models\query\UserPlanActionQuery(get_called_class());
    }
    
    public static function hasAction($planPool, $action) {
        $m = UserPlanAction::find()->where(["plan_pool_id"=>$planPool->id, "action_status"=>$action])->one();
        return $m;
    }

    public function getPlanActionPhoto() {
        return $this->hasOne(UserPlanActionDocument::class, ['plan_action_id' => 'id']);
    }

    public function toObject() {
        $m = $this;

        $o = (object) [];
        $o->plan_pool_id = $m->plan_pool_id;
        $o->notes_user = $m->notes_user;
        $o->notes = $m->notes;
        $o->action_status = $m->action_status;
        $o->created_at = $m->created_at;

        $planActionPhotos = [];
        if($m->action_status == SELF::ACTION_PHYSICAL_ASSESSMENT || $m->action_status == SELF::ACTION_UPLOAD_PHOTO){

            $actionPhotos = UserPlanActionDocument::find()->andWhere(['plan_action_id' => $m->id])->all();
            if ($actionPhotos) {
                $photos = [];
                for ($i=0; $i < count($actionPhotos); $i++) { 
                    // array_push($photos, ["uri"=>$actionPhotos[$i]->thumbnail_base_url . "/" . $actionPhotos[$i]->thumbnail_path]);
                    $preSignImage = $actionPhotos[$i]->thumbnail_path ? Utility::getPreSignedS3Url("thumbnail_square/".$actionPhotos[$i]->thumbnail_path) : "";

                    array_push($photos, ["thumbnail_presigned" => $preSignImage, "created_by" => $actionPhotos[$i]->created_by]);
                }
                $planActionPhotos = $photos;
            }
        }
        $o->planActionPhotos = $planActionPhotos;

        return $o;
    }

    public static function makeModel($planPool, $action_status, $notes_user="", $notes="") {
        $m = new SELF();
        $m->plan_pool_id = $planPool->id;
        $m->action_status = $action_status;
        $m->notes_user = $notes_user;
        $m->notes = $notes;
        return $m;
    }

}
