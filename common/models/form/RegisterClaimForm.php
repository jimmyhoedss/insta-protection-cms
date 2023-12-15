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
            //$plan = InstapPlan::find()->Where(["id"=>$planPool->plan_id])->one();

            if (Yii::$app->user->can('editOwnModel', ['model' => $planPool, 'attribute'=>'user_id'])) {
                if($planPool->plan_status == InstapPlanPool::STATUS_ACTIVE){
                    $exist = UserCase::hasCase($planPool);
                    if ($exist) {
                        $this->addError('plan_pool_id', Yii::t('common', "already registered."));
                        return null;
                    }
                    
                    //$transaction = Yii::$app->db->beginTransaction();

                    $case = UserCase::makeModel($planPool, UserCase::CASE_STATUS_CLAIM_PENDING, $this->device_issue, $this->location, $this->occurred_at, $this->contact_alt, $this->claim_type);
                    return $case;
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
