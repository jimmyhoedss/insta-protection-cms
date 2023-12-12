<?php

namespace common\models;

use Yii;
use common\components\MyCustomActiveRecord;
use common\models\UserCase;
use common\models\QcdClaimRegistration;
use console\controllers\SysController;
use api\components\CustomHttpException;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "user_case_action".
 *
 * @property int $id
 * @property int $case_id
 * @property string $notes_user
 * @property string $notes
 * @property string $action_status
 * @property string $status
 * @property int $created_at
 * @property int $created_by
 * @property int $updated_at
 * @property int $updated_by
 */
class UserCaseAction extends MyCustomActiveRecord
{
    const ACTION_CLAIM_SUBMIT = "claim_submit";
    const ACTION_CLAIM_UPLOAD_PHOTO = "claim_upload_photo";
    const ACTION_CLAIM_REQUIRE_CLARIFICATION = "claim_require_clarification";
    const ACTION_CLAIM_REGISTRATION_RESUBMIT = "claim_resubmit";

    const ACTION_CLAIM_PROCESSING = 'claim_processing';    
    const ACTION_CLAIM_REPAIR_IN_PROGRESS = 'claim_repair_in_progress';    
    const ACTION_CLAIM_REPAIR_COMPLETED = 'claim_repair_completed';

    const ACTION_CLAIM_CLOSED = 'claim_closed';
    const ACTION_CLAIM_CANCELLED = 'claim_cancelled';
    const ACTION_CLAIM_REJECTED = 'claim_rejected';
    //const ACTION_SERVICE_PROVIDER_REGISTERED = 'service_provider_registered'; //service_provider_registered (INTERNAL APPROVE FOR CLAIM)
    //const ACTION_SERVICE_PROVIDER_DISCHARGED = 'service_provider_discharged';

    public static function tableName()
    {
        return 'user_case_action';
    }

     public function rules()
    {
        return ArrayHelper::merge([
            [['case_id', 'action_status'], 'required'],
            [['case_id', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['notes_user', 'notes', 'action_status', 'status'], 'string'],
            ['action_status', 'in', 'range' => array_keys(self::allActionStatus())],
            ['notes_user', 'required', 'when' => function($model) {
                $keys = array_keys(self::requireNotesActionStatus());
                return in_array($model->action_status, $keys);
            }]
        ], parent::rules());
    }

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'case_id' => Yii::t('common', 'Case ID'),
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
            self::ACTION_CLAIM_SUBMIT => Yii::t('common', 'Submit Claim'),
            self::ACTION_CLAIM_UPLOAD_PHOTO => Yii::t('common', 'Upload Photo'),
            self::ACTION_CLAIM_REQUIRE_CLARIFICATION => Yii::t('common', "Require Clarification"),
            self::ACTION_CLAIM_REGISTRATION_RESUBMIT => Yii::t('common', "Re-submit"),
            self::ACTION_CLAIM_PROCESSING => Yii::t('common', 'Processing'),
            self::ACTION_CLAIM_REPAIR_IN_PROGRESS => Yii::t('common', 'Repair In Progress'), 
            self::ACTION_CLAIM_REPAIR_COMPLETED => Yii::t('common', 'Repair Completed'),
            self::ACTION_CLAIM_CLOSED => Yii::t('common', 'Closed'),
            self::ACTION_CLAIM_CANCELLED => Yii::t('common', 'Cancelled'),
            self::ACTION_CLAIM_REJECTED => Yii::t('common', 'Rejected'),
        ];

    }
    public static function processClaimActionStatus() {
        return [
            self::ACTION_CLAIM_REQUIRE_CLARIFICATION => self::allActionStatus()[self::ACTION_CLAIM_REQUIRE_CLARIFICATION],
            self::ACTION_CLAIM_PROCESSING => self::allActionStatus()[self::ACTION_CLAIM_PROCESSING],
            self::ACTION_CLAIM_REPAIR_IN_PROGRESS => self::allActionStatus()[self::ACTION_CLAIM_REPAIR_IN_PROGRESS],
            self::ACTION_CLAIM_REPAIR_COMPLETED => self::allActionStatus()[self::ACTION_CLAIM_REPAIR_COMPLETED],
            self::ACTION_CLAIM_CLOSED => self::allActionStatus()[self::ACTION_CLAIM_CLOSED],
            self::ACTION_CLAIM_CANCELLED => self::allActionStatus()[self::ACTION_CLAIM_CANCELLED],
            self::ACTION_CLAIM_REJECTED => self::allActionStatus()[self::ACTION_CLAIM_REJECTED],
        ];
    }    
    public static function requireNotesActionStatus() {
        return [
            self::ACTION_CLAIM_REQUIRE_CLARIFICATION => self::allActionStatus()[self::ACTION_CLAIM_REQUIRE_CLARIFICATION],
            self::ACTION_CLAIM_CANCELLED => self::allActionStatus()[self::ACTION_CLAIM_CANCELLED],
            self::ACTION_CLAIM_REJECTED => self::allActionStatus()[self::ACTION_CLAIM_REJECTED],
        ];
    }



    public static function makeModel($case, $action_status, $notes = "", $notes_user = "") {
        $m = new SELF;
        $m->case_id = $case->id;
        $m->notes_user = $notes_user;
        $m->notes = $notes;
        $m->action_status = $action_status;
        return $m;
    }

    public static function hasAction($case, $action) {
        $m = SELF::find()->where(["case_id"=>$case->id, "action_status"=>$action])->one();
        return $m;
    }

    public function getUserCase() {
        return $this->hasOne(UserCase::class, ['id' => 'case_id']);
    }


}
