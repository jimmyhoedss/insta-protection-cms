<?php
namespace common\models\form;

use Yii;
use yii\base\Model;
use common\models\InstapPlanPool;
use common\models\User;
use common\models\UserPlan;
use common\models\UserPlanAction;
use common\models\UserPlanActionDocument;
use common\models\UserProfile;
use common\models\SysSocketNotification;

use common\components\Utility;
use api\components\CustomHttpException;

class DeviceAssessmentForm extends Model
{
    public $mobile_number_full;
    public $provisional_token;
    public $plan_pool_id;
    public $dealer_user_id = null; //pass over from react when dealer help to scan

    public $image_file = [];

    public function rules()
    {
        return [
            [['mobile_number_full', 'provisional_token', 'plan_pool_id', 'image_file'], 'required'],
            ['plan_pool_id', 'exist', 'targetClass' => InstapPlanPool::class, 'targetAttribute' => ['plan_pool_id' => 'id']],
            ['mobile_number_full', 'exist', 'targetClass' => User::class, 'targetAttribute' => ['mobile_number_full' => 'mobile_number_full']],            
            ['dealer_user_id', 'integer'],
            //[['device_token'], 'string', 'min' => 64, 'max' => 64],
            [['image_file'], 'image', 'skipOnEmpty' => false, 'extensions' => 'png, jpg, jpeg', 'maxSize'=>1024 * 1024 * 25, 'maxFiles' => 2, 'minFiles' => 2]
        ];
    }



    public function assess()
    {
        $planPool = InstapPlanPool::find()->Where(["id"=>$this->plan_pool_id])->one();

        $allow_status = [InstapPlanPool::STATUS_PENDING_REGISTRATION, InstapPlanPool::STATUS_PENDING_APPROVAL];
        if (!in_array($planPool->plan_status, $allow_status)) {
            $this->addError('plan_pool_id', Yii::t('common', "Device assessment failed. Plan status is")." [". $planPool->plan_status ."]");
            return null;
        }
        $user = User::find()->andWhere(['provisional_token' => $this->provisional_token])->andWhere(['mobile_number_full' => $this->mobile_number_full])->one();
        if (!$user) {
            // $this->addError('plan_pool_id', Yii::t('common', "Invalid QR Code."));
            $this->addError('plan_pool_id', Yii::t('common', "Cannot verify device assessment, invalid token."));
            return null;
        } 
        $created_by = $this->dealer_user_id != null ? $this->dealer_user_id : $user->id;

        $success = false; 
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $m = UserPlanAction::makeModel($planPool, UserPlanAction::ACTION_PHYSICAL_ASSESSMENT);
            $m->getBehavior('blame')->value = $created_by;
            $m->save();

            if ($m->hasErrors()) {
                $msg = print_r($m->getErrors(),true) ;
                throw new \Exception($msg);
            }

            $arr = $this->uploadPhotos();
            foreach($arr as $item) {
                $p = UserPlanActionDocument::makeModel($m, $item, UserPlanActionDocument::TYPE_DEVICE_ASSESSMENT);
                $p->getBehavior('blame')->value = $created_by;
                if(!$p->save()) {
                    throw new \Exception("Cannot update assessment photo.");
                }
            }        
            //oh:: comment for partial submit
            // $complete = $planPool->checkRegtrationChecklistComplete();
            // if ($complete) {
            //     $planPool->plan_status = InstapPlanPool::STATUS_PENDING_APPROVAL;
            //     if (!$planPool->save()) {
            //         throw new \Exception("Cannot update plan status.");
            //     }                    
            // } 
            
            $success = true;   


        } catch (yii\db\IntegrityException $e) {
            Yii::error($e->getMessage(), 'DeviceAssessmentForm');
        } catch ( \Exception $e ) {
            Yii::error($e->getMessage(), 'DeviceAssessmentForm');
        }


        if($success) {
            $transaction->commit();
            $socketNotication = SysSocketNotification::RESULT_SUCCESS;            
        } else {
            $transaction->rollback();                
            $socketNotication = SysSocketNotification::RESULT_FAIL;            
            $this->addError('plan_pool_id', Yii::t('common', "Cannot update device assessment."));
            $planPool = null;
        }

        try {
            $socket = SysSocketNotification::makeModel(SysSocketNotification::NOTIFY_DEVICE_ASSESSMENT, $user->id, $socketNotication);
            $socket->send();
        } catch (\Exception $e) {
            Yii::error($e->getMessage(), 'DeviceAssessmentForm SysSocketNotification');
        }

        return $planPool;  

    }

    private function uploadPhotos() {

        $uploadAction = new \trntv\filekit\actions\UploadAction("uploads",$this);
        $uploadAction->uploadPath = "media/device";
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
