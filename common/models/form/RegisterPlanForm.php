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
use common\models\UserPlanDetail;
use common\models\DealerUser;
use common\models\DealerOrder;

use common\components\Utility;
use api\components\CustomHttpException;

class RegisterPlanForm extends Model
{
    //Loynote:: why scenario is not in UserPlanDetail? So you will not so many werid rules here.
    const SCENARIO_BOTH = "scenario_both"; 
    const SCENARIO_PHOTO = "scenario_photo";
    const SCENARIO_PARTIAL = "scenario_partial_submit";
    
    public $plan_pool_id;
    public $plan_category;
    public $policy_number;

    public $sp_brand;
    public $sp_model_number;
    // public $sp_model_name;
    public $sp_serial;
    public $plan_id;
    public $sp_imei;
    public $sp_color;
    public $sp_dealer_code;
    public $sp_country_of_purchase;
    public $sp_device_purchase_date;
    public $sp_device_purchase_price;
    public $sp_device_capacity;

    //use for partial reg, check if frontend submit through submit button instead of submit from lose focus


    public $image_file = [];

    public function rules()
    {
        //Loynote:: Try to use rules to check instead
        // $r0 = [
        //     [['plan_pool_id'], 'unique', 'targetClass'=> UserPlanDetail::className(), 'message' => '{attribute} already registered.', 'on' => SELF::SCENARIO_BOTH],
        // ];
        $r1 = UserPlanDetail::userPlanDetailRules();
        $r1[0] = array_merge(UserPlanDetail::userPlanDetailRules()[0], ['except' => [SELF::SCENARIO_PHOTO, SELF::SCENARIO_PARTIAL]]);
        $r1[1] = array_merge(UserPlanDetail::userPlanDetailRules()[1], ['except' => [SELF::SCENARIO_PHOTO, SELF::SCENARIO_PARTIAL, SELF::SCENARIO_BOTH]]);
        //oh:: Ignore check serial and imei unique when doing partial reg
        $r1[5] = array_merge(UserPlanDetail::userPlanDetailRules()[5], ['except' => [SELF::SCENARIO_PARTIAL]]);
        $r1[6] = array_merge(UserPlanDetail::userPlanDetailRules()[6], ['except' => [SELF::SCENARIO_PARTIAL]]);
        $r1[7] = array_merge(UserPlanDetail::userPlanDetailRules()[7], ['except' => [SELF::SCENARIO_PARTIAL]]);
        $r2 = [
            ['plan_pool_id', 'exist', 'targetClass' => InstapPlanPool::class, 'targetAttribute' => ['plan_pool_id' => 'id']],
            [['image_file'], 'required', 'on' =>[SELF::SCENARIO_PHOTO, SELF::SCENARIO_BOTH] ],
            [['image_file'], 'image', 'on' =>[SELF::SCENARIO_PHOTO, SELF::SCENARIO_BOTH], 'skipOnEmpty' => false, 'extensions' => 'png, jpg, jpeg', 'maxSize'=>1024 * 1024 * 25, 'maxFiles' => 5]

        ];
        return array_merge($r1,$r2);
    }

    public function registerPlan() {
        $planPool = InstapPlanPool::find()->Where(["id"=>$this->plan_pool_id])->one();

        if ($planPool->plan_status != InstapPlanPool::STATUS_PENDING_REGISTRATION) {
            $this->addError('plan_pool_id', Yii::t('common', "Cannot register. Plan status is")." [". $planPool->plan_status . "]");
            return null;
        }
        /*if($planPool->user->email_status != User::EMAIL_STATUS_VERIFIED) {
            $this->addError('plan_pool_id', Yii::t('common', "Please verify email first."));
            return null;
        }*/

        $success = false;
        $transaction = Yii::$app->db->beginTransaction();

        try {

            $m1 = UserPlanAction::makeModel($planPool, UserPlanAction::ACTION_REGISTRATION);
            $m2 = UserPlanDetail::makeModel($planPool, $this);

            //need to check because partial reg already have this action
            $hasRegAction = UserPlanAction::hasAction($planPool, UserPlanAction::ACTION_REGISTRATION);
            if(!$hasRegAction) {
                $m1->save();
            }
            $m2->save();

            if ($m1->hasErrors() || $m2->hasErrors()) {
                $string = array_merge(array_merge($m1->getErrors(), $m2->getErrors()));
                $msg = serialize($string);

                throw new \Exception($msg);
            }
               
            $hasUploadAction = UserPlanAction::hasAction($planPool, UserPlanAction::ACTION_UPLOAD_PHOTO);
            if(!$hasUploadAction) {
                $m3 = UserPlanAction::makeModel($planPool, UserPlanAction::ACTION_UPLOAD_PHOTO);
                if(!$m3->save()) {
                    $string = serialize($m3->getErrors());
                    throw new \Exception($string);
                }
                $arr = $this->uploadPhotos();
                for ($i=0; $i < count($arr); $i++) {
                    $item = $arr[$i];
                    $p = UserPlanActionDocument::makeModel($m3, $item, UserPlanActionDocument::TYPE_REGISTRATION);
                    if(!$p->save()) {
                        throw new \Exception(Yii::t("common","Cannot update plan photo."));
                    }
                }

            }
            
            $complete = $planPool->checkRegtrationChecklistComplete();
            if ($complete) {
                $planPool->plan_status = InstapPlanPool::STATUS_PENDING_APPROVAL;
                if (!$planPool->save()) {
                    throw new \Exception(Yii::t("common", "Cannot update plan status."));
                }                    
            } else {
                throw new \Exception(Yii::t("common", "Registration not complete."));
            }
            
            $success = true;                

        } catch (yii\db\IntegrityException $e) {
            Yii::error($e->getMessage(), 'RegisterPlanForm');
        } catch ( \Exception $e ) {
            // print_r("exit");exit();
            $array = unserialize($e->getMessage());
            $this->addErrors($array);
            Yii::error($e->getMessage(), 'RegisterPlanForm');
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
    //submit detail without check any rules
    public function registerPartially() {
        $planPool = InstapPlanPool::find()->Where(["id"=>$this->plan_pool_id])->one();
        if ($planPool->plan_status != InstapPlanPool::STATUS_PENDING_REGISTRATION) {
            $this->addError('plan_pool_id', Yii::t("common", "Cannot register. Plan status is")." [". $planPool->plan_status ."]");
            return null;
        }
        $success = false;
        $transaction = Yii::$app->db->beginTransaction();

        try {

            $m1 = UserPlanAction::makeModel($planPool, UserPlanAction::ACTION_REGISTRATION);
            $m2 = UserPlanDetail::makeModel($planPool, $this);
            // $m3 = UserPlanAction::makeModel($planPool, UserPlanAction::ACTION_UPLOAD_PHOTO);
            if($this->image_file) {
                //apply for partial submit save if image =2
                if(count($this->image_file) == 2) {
                    $hasUploadAction = UserPlanAction::hasAction($planPool, UserPlanAction::ACTION_UPLOAD_PHOTO);
                    if(!$hasUploadAction) {
                        $m3 = UserPlanAction::makeModel($planPool, UserPlanAction::ACTION_UPLOAD_PHOTO);
                        if(!$m3->save()) {
                            $string = serialize($m3->getErrors());
                            throw new \Exception($string);
                        }
                        $arr = $this->uploadPhotos();
                        for ($i=0; $i < count($arr); $i++) {
                            $item = $arr[$i];
                            $p = UserPlanActionDocument::makeModel($m3, $item, UserPlanActionDocument::TYPE_REGISTRATION);
                            if(!$p->save()) {
                                throw new \Exception("Cannot update plan photo.");
                            }
                        }

                    }
                }
            }

            $hasRegAction = UserPlanAction::hasAction($planPool, UserPlanAction::ACTION_REGISTRATION);
            if(!$hasRegAction) {
                $m1->save();
            }
            $m2->save(false); //ignore the rules
            $planPool->plan_status = InstapPlanPool::STATUS_PENDING_REGISTRATION;

            if ($m1->hasErrors() || $m2->hasErrors()) {
                $string = array_merge(array_merge($m1->getErrors(), $m2->getErrors()));
                $msg = serialize($string);

                throw new \Exception($msg);
            }
            
            if (!$planPool->save()) {
                throw new \Exception("Cannot update plan status.");
            }  
            
            $success = true;                

        } catch (yii\db\IntegrityException $e) {
            Yii::error($e->getMessage(), 'RegisterPlanForm');
        } catch ( \Exception $e ) {
            // print_r($e->getMessage());exit();
            $array = unserialize($e->getMessage());
            $this->addErrors($array);
            Yii::error($e->getMessage(), 'RegisterPlanForm');
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
    //Loynote:: remove checks for preventing other user to register photo... anyone should be able to help to upload photo.
    public function registerPhoto() {
        $planPool = InstapPlanPool::find()->Where(["id"=>$this->plan_pool_id])->one();

        $success = false;
        $transaction = Yii::$app->db->beginTransaction();

        try {

            $m = UserPlanAction::makeModel($planPool, UserPlanAction::ACTION_UPLOAD_PHOTO);
            $m->save();

            if ($m->hasErrors()) {
                $msg = print_r($m->getErrors(),true);
                throw new \Exception($msg);
            }

            $arr = $this->uploadPhotos();
            foreach($arr as $item) {
                $p = UserPlanActionDocument::makeModel($m, $item, UserPlanActionDocument::TYPE_REGISTRATION);
                if(!$p->save()) {
                    throw new \Exception("Cannot upload photo.");
                }
            }   
        
            // $complete = $planPool->checkRegtrationChecklistComplete();
            // if ($complete) {
            //     $planPool->plan_status = InstapPlanPool::STATUS_PENDING_APPROVAL;
            //     if (!$planPool->save()) {
            //         throw new \Exception("Cannot update plan status.");
            //     }                    
            // } 
            
            $success = true;                

        } catch (yii\db\IntegrityException $e) {
            Yii::error($e->getMessage(), 'registerPhoto');
        } catch ( \Exception $e ) {
            Yii::error($e->getMessage(), 'registerPhoto');
        }

        if ($success) {
            $transaction->commit();                
        } else {
            $transaction->rollback();
            $this->addError('plan_pool_id', Yii::t('common', "Cannot upload photo."));
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
