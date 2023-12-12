<?php
namespace common\models\form;

use Yii;
use yii\base\Model;
use common\models\InstapPlanPool;
use common\models\User;
use common\models\UserPlan;
use common\models\UserPlanAction;
use common\models\UserPlanActionLog;
use common\models\UserPlanActionDocument;
use common\models\UserProfile;
use common\models\UserPlanDetail;

use common\components\Utility;
use api\components\CustomHttpException;

class RegistrationResubmitForm extends Model
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
        $planPool = InstapPlanPool::find()->Where(["id"=>$this->plan_pool_id])->one();

        $allow_status = [InstapPlanPool::STATUS_REQUIRE_CLARIFICATION];
        if (!in_array($planPool->plan_status, $allow_status)) {
            $this->addError('plan_pool_id', Yii::t('common', "Device assessment failed. Plan status is")." [".$planPool->plan_status ."]");
            return null;
        }

        $success = false; 
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $m = UserPlanAction::makeModel($planPool, UserPlanAction::ACTION_REGISTRATION_RESUBMIT, $this->description);
            $actionLog = UserPlanActionLog::makeModel($planPool, UserPlanAction::ACTION_REGISTRATION_RESUBMIT);
            $m->save();
            $actionLog->save();

            if ($m->hasErrors() || $actionLog->hasErrors()) {
                $msg = print_r($m->getErrors(),true) . print_r($actionLog->getErrors(),true);
                throw new \Exception($msg);
            }
            // print_r($this->image_file);
            // exit();
            if(count($this->image_file) > 0) {
                $arr = $this->uploadPhotos();
                for ($i=0; $i < count($arr); $i++) {
                    $item = $arr[$i];
                    $p = UserPlanActionDocument::makeModel($m, $item, UserPlanActionDocument::TYPE_REGISTRATION_RESUBMIT);
                    if(!$p->save()) {
                        throw new \Exception("Cannot update plan photo.");
                    }
                }
            }
        
            $planPool->updateAttributes(["plan_status"=>InstapPlanPool::STATUS_PENDING_APPROVAL]);           
            $success = true;   


        } catch (yii\db\IntegrityException $e) {
            Yii::error($e->getMessage(), 'RegistrationResubmitForm');
        } catch ( \Exception $e ) {
            Yii::error($e->getMessage(), 'RegistrationResubmitForm');
        }           

        if ($success) {
            $transaction->commit();                
        } else {
            $transaction->rollback();
            $this->addError('plan_pool_id', Yii::t('common', "Cannot update plan."));
            $planPool = null;
        } 

        return $planPool;
    }

    private function uploadPhotos() {

        $uploadAction = new \trntv\filekit\actions\UploadAction("uploads",$this);
        $uploadAction->uploadPath = "media/user-plan";
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

}