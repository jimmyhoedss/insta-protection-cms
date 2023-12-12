<?php
namespace common\models\form;

use Yii;
use yii\base\Model;
use common\models\InstapPlanPool;
use common\models\User;
use common\models\UserPlan;
use common\models\UserCase;
use common\models\UserCaseAction;
use common\models\UserCaseActionLog;
use common\models\UserCaseActionDocument;
use common\models\UserProfile;
use common\models\UserPlanDetail;

use common\components\Utility;
use api\components\CustomHttpException;

class RegistrationResubmitClaimForm extends Model
{
    const SCENARIO_DETAIL = "scenario_detail";
    const SCENARIO_PHOTO = "scenario_photo";
    const SCENARIO_BOTH = "scenario_both";

    public $plan_pool_id;
    public $description = "";
    public $image_file = [];

    public function rules()
    {
        return [
            [['description'], 'required', 'on'=>[self::SCENARIO_DETAIL,self::SCENARIO_BOTH]],
            [['description'], 'string'],
            [['plan_pool_id'], 'required'],
            [['plan_pool_id'], 'integer'],
            ['plan_pool_id', 'exist', 'targetClass' => InstapPlanPool::class, 'targetAttribute' => ['plan_pool_id' => 'id']],
             [['description'], 'filter', 'filter' => '\yii\helpers\HtmlPurifier::process'],
            [['image_file'], 'required', 'on'=>[self::SCENARIO_PHOTO,self::SCENARIO_BOTH]],
            [['image_file'], 'image', 'skipOnEmpty' => false, 'extensions' => 'png, jpg, jpeg', 'maxSize'=>1024 * 1024 * 25, 'maxFiles' => 5, 'on'=>[self::SCENARIO_PHOTO,self::SCENARIO_BOTH]]
    
        ];
    }

    public function resubmit()
    {
        if ($this->validate()) {

            $case = UserCase::find()->where(['in', 'current_case_status', UserCase::statusNotReject()])->andWhere(["plan_pool_id" => $this->plan_pool_id])->orderBy(['created_at'=>SORT_DESC])->limit(1)->one();

            //check is owner of plan
            //loynote: rules need to add to indivdual?
            if (Yii::$app->user->can('editOwnModel', ['model' => $case, 'attribute'=>'user_id'])) {
                $transaction = Yii::$app->db->beginTransaction();
                
                // comment check of existing for multiple require clarification
                // $exist = UserPlanAction::hasAction($planPool, UserPlanAction::ACTION_REGISTRATION_RESUBMIT);
                // if ($exist) {
                //     $msg = "already resubmitted.";
                //     $this->addError('plan_pool_id', Yii::t('common', $msg));
                //     return null;
                // }
                    
                $m = UserCaseAction::makeModel($case, UserCaseAction::ACTION_CLAIM_REGISTRATION_RESUBMIT, "", $this->description);
                $actionLog = UserCaseActionLog::makeModel($case, UserCaseAction::ACTION_CLAIM_REGISTRATION_RESUBMIT);

                try {

                    if ($m->save() && $actionLog->save()) {

                        if (count($this->image_file) > 0) {
                            $arr = $this->uploadPhotos();
                            // print_r($arr);
                            // exit();
                            //loop & save all photos to model
                            for ($i=0; $i < count($arr); $i++) {
                                $item = $arr[$i];
                                $p = UserCaseActionDocument::makeModel($m, $item, UserCaseActionDocument::TYPE_INCIDENT_RESUBMIT);
                                if(!$p->save()) {
                                    throw CustomHttpException::internalServerError(Yii::t('common', "Cannot update case photo."));
                                }                        
                            }
                        }
                    
                        $case->updateAttributes(["current_case_status"=>UserCase::CASE_STATUS_CLAIM_PENDING]);

                        $transaction->commit();
                        return $case;
                    } else {
                        throw CustomHttpException::internalServerError(Yii::t('common', "Cannot update plan detail."));
                    }
                    

                } catch (yii\db\IntegrityException $e) {
                    $transaction->rollback();
                    throw CustomHttpException::internalServerError(Yii::t('common', "Cannot update plan."));
                }
            
            } else {
                $this->addError('plan_pool_id', Yii::t('common', "Not allowed."));
            }
        
        }

        return null;
    }

    private function uploadPhotos() {

        $uploadAction = new \trntv\filekit\actions\UploadAction("uploads",$this);
        $uploadAction->uploadPath = "media/case/claim_resubmit";
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
            'description' => Yii::t('common', 'Description'),

        ];
    }

}