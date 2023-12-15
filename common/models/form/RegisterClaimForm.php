<?php
namespace common\models\form;

use Yii;
use yii\base\Model;
use yii\helper\ArrayHelper;
use yii\web\UploadedFile;

use common\models\InstapPlan;
use common\models\InstapPlanPool;
use common\models\UserCase;
use common\models\UserCaseAction;
use common\models\UserCaseActionLog;
use common\models\UserCaseActionDocument;
use common\models\UserCaseRepairCentre;
use common\models\UserCaseRetailStore;
use common\models\QcdClaimRegistration;
use common\models\QcdRepairCentre;
use common\models\QcdRetailStore;
use common\models\fcm\FcmCaseStatusChanged;

use common\jobs\EmailQueueJob;

use common\components\Utility;
use api\components\CustomHttpException;

class RegisterClaimForm extends Model
{
    public $claim_type;
    public $plan_pool_id;
    public $device_issue;
    public $repair_centre_id;
    public $retail_store_id;
    public $check;
    public $location;
    public $occurred_at;
    public $contact_alt;

    public $image_file = [];

    const SCENARIO_CMS_FORM = "scenario_cms_form";

    public function actions(){
        return [
           'upload'=>[
               'class'=>'trntv\filekit\actions\UploadAction',
           ]
       ];
    }
    public function rules()
    {
        return [
            [['claim_type', 'plan_pool_id', 'device_issue', 'repair_centre_id', 'location', 'occurred_at','contact_alt'], 'required'],
            ['check', 'required', 'on'=>SELF::SCENARIO_CMS_FORM],
            ['check',function ($attribute) {
                if(count($this->$attribute) < 3){
                    $this->addError('check', Yii::t('common', 'Please agree to all disclaimers'));
                    return false;
                }
            }, 'on'=>SELF::SCENARIO_CMS_FORM],
            [['plan_pool_id', 'repair_centre_id', 'retail_store_id', 'occurred_at'], 'integer'],
            [['claim_type', 'location', 'contact_alt'], 'string'],
            [['device_issue'], 'string', 'max' => 500],
            [['device_issue', 'location', 'contact_alt', 'claim_type'], 'filter', 'filter' => '\yii\helpers\HtmlPurifier::process'],
            [['image_file'], 'image', 'skipOnEmpty' => false, 'extensions' => 'png, jpg, jpeg', 'maxSize'=>1024 * 1024 * 25, 'maxFiles' => 5],
        ];
    }
    public function registerClaim() {
        if ($this->validate()) {

            $planPool = InstapPlanPool::find()->Where(["id"=>$this->plan_pool_id])->one();
            $plan = InstapPlan::find()->Where(["id"=>$planPool->plan_id])->one();

            if (Yii::$app->user->can('editOwnModel', ['model' => $planPool, 'attribute'=>'user_id'])) {
                if($planPool->plan_status == InstapPlanPool::STATUS_ACTIVE){
                    $exist = UserCase::hasCase($planPool);
                    if ($exist) {
                        $this->addError('plan_pool_id', Yii::t('common', "already registered."));
                        return null;
                    }
                    
                    $transaction = Yii::$app->db->beginTransaction();

                    $case = UserCase::makeModel($planPool, UserCase::CASE_STATUS_CLAIM_PENDING, $this->device_issue, $this->location, $this->occurred_at, $this->contact_alt, $this->claim_type);
                    if($case->save()){
                        $caseAction = UserCaseAction::makeModel($case, UserCaseAction::ACTION_CLAIM_SUBMIT);
                        $caseActionPhoto = UserCaseAction::makeModel($case, UserCaseAction::ACTION_CLAIM_UPLOAD_PHOTO);
                        //save log for dashboard plotting
                        $actionLog = UserCaseActionLog::makeModel($case, UserCaseAction::ACTION_CLAIM_SUBMIT);

                        // $repairCentreDetails = UserCaseRepairCentre::getRepairCentreDetails($this->repair_centre_id);
                        $repairCentre = QcdRepairCentre::find()->where(['id' =>$this->repair_centre_id ])->one();
                        if($repairCentre){
                            $repairCentre = UserCaseRepairCentre::makeModel($case, $planPool->plan_category, $repairCentre);
                        } else {
                            $this->addError('repair_centre_id', Yii::t('common', "Invalid repair centre id."));
                            $transaction->rollback();
                            return null;
                        }

                        $retailStoreSave = true;
                        if(($this->claim_type == 'replacement' || $this->claim_type == 'upgrade') && $plan->tier == 'ultimate_plus') {
                            $retailStore = QcdRetailStore::find()->where(['id' => $this->retail_store_id ])->one();
                            if($retailStore){
                                $retailStore = UserCaseRetailStore::makeModel($case, $planPool->plan_category, $retailStore);
                            } else {
                                $this->addError('retail_store_id', Yii::t('common', "Invalid retail store id."));
                                $transaction->rollback();
                                return null;
                            }
                            $retailStoreSave = $retailStore->save();
                        }
                        $returnData = $retailStoreSave;
                        $transaction->rollback();
                        return $returnData;
                        if($caseAction->save() && $repairCentre->save() && $retailStoreSave && $caseActionPhoto->save() && $actionLog->save()) {
                            
                            $planPool->updateAttributes(["plan_status"=>InstapPlanPool::STATUS_PENDING_CLAIM]);

                            try {
                              
                                $arr = $this->uploadPhotos();
                                //loop & save all photos to model
                                for ($i=0; $i < count($arr); $i++) {
                                    $item = $arr[$i];
                                    $p = UserCaseActionDocument::makeModel($caseActionPhoto, $item, UserCaseActionDocument::TYPE_INCIDENT);
                                    if(!$p->save()) {
                                        throw CustomHttpException::internalServerError(Yii::t('common',"Cannot update case photo."));
                                    }                        
                                }
                                //$fcm = new FcmCaseStatusChanged($case);
                                //$fcm->send();
                                // if ($modelAction->action_status == UserCaseAction::ACTION_CLAIM_REQUIRE_CLARIFICATION) {
                                    //ToDo: sent email to user when case status is require clarification
                                    //get language session and change the email language accordingly
                                     /*Yii::$app->queue->delay(0)->push(new EmailQueueJob([
                                        'subject' => Yii::t('frontend', '[InstaProtection] Claim Submission Received'),
                                        'view' => 'claimSubmissionReceived',
                                        'language' => 'id-ID',
                                        'to' => $planPool->user->email,
                                        'params' => [
                                            'user' => 'Sir/ Madam',
                                        ]
                                    ]));*/

                                // }
                                // print_r($fcm);
                                // exit();
                                $transaction->commit();
                                return $case;

                            } catch (yii\db\IntegrityException $e) {
                                $transaction->rollback();
                                throw CustomHttpException::internalServerError(Yii::t('common',"Cannot update case photo."));
                            }
                            throw CustomHttpException::internalServerError(Yii::t('common',"Cannot update case action."));
                        }
                    }
                } else {
                    $this->addError('plan_pool_id', Yii::t('common', "Plan not approved."));
                }

            } else {
                $this->addError('plan_pool_id', Yii::t('common', "Not allowed."));
            } 

        }

        return null;
    }
    

    private function uploadPhotos() {

        $uploadAction = new \trntv\filekit\actions\UploadAction("uploads",$this);
        $uploadAction->uploadPath = "media/case/claim_submit";
        $uploadAction->fileparam = "image_file";
        $uploadAction->multiple = true;

        $data = [];

        $res = $uploadAction->run();
        $files = $res['files'];
        $files_count = count($files);
        for ($i=0; $i < $files_count; $i++) { 
            $path = $files[$i]['path'];
            $path =  str_replace('\\', '/', $path);                
            $temp = [
                'base_url' => $files[$i]['base_url'],
                'path' => $path
            ];
            array_push($data, $temp);
        }

        return $data;
    }

   
    public function attributeLabels()
    {
        return [
            'plan_pool_id' => Yii::t('common', 'Plan Pool ID'),
            'device_issue' => Yii::t('common', 'Device Issue'),
            'repair_centre_id' => Yii::t('common', 'Repair Centre'),
            'retail_store_id' => Yii::t('common', 'Retail Store'),
            'check' => Yii::t('common', 'Disclaimers'),
            'occurred_at' => Yii::t('common', 'Occurred At'),
            'location' => Yii::t('common', 'Location'),

        ];
    }

}
