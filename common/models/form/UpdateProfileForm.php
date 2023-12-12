<?php
namespace common\models\form;

use yii\base\Model;
use Yii;
use yii\web\JsExpression;
use common\models\User;
use common\models\SysUserToken;
use common\models\SysSesTrace;
use common\components\Utility;
use api\components\CustomHttpException;


class UpdateProfileForm extends Model
{
    public $image_file;
    public $first_name;
    public $last_name;
    public $email;
    public $gender;
    public $birthday;

    public $user;
    public $userProfile;

    const SCENARIO_DETAIL = "scenario_detail";
    const SCENARIO_PHOTO = "scenario_photo";
    const SCENARIO_BOTH = "scenario_both";

    public function rules()
    {
        return [
            [['first_name', 'last_name', 'email', 'gender', 'birthday'], 'required' , 'on'=>[self::SCENARIO_DETAIL,self::SCENARIO_BOTH]],
            [['gender'], 'integer'],
            [['email'], 'email'],
            [['first_name', 'last_name', 'birthday'], 'string', 'max' => 45],
            [['first_name', 'last_name'], 'filter', 'filter' => '\yii\helpers\HtmlPurifier::process'],
            [['image_file'], 'required', 'on'=>[self::SCENARIO_PHOTO,self::SCENARIO_BOTH]],
            [['image_file'], 'image', 'skipOnEmpty' => false, 'extensions' => 'png, jpg, jpeg', 'maxSize'=>1024 * 1024 * 25, 'maxFiles' => 5, 'on'=>[self::SCENARIO_PHOTO,self::SCENARIO_BOTH]]
        ];
    }

    public function update(){
        $this->user = Yii::$app->user->identity;
        $this->userProfile = $this->user->userProfile;
        switch ($this->scenario) {
            case self::SCENARIO_DETAIL:
                return $this->updateDetail();
            
            case self::SCENARIO_PHOTO:
                return $this->updatePhoto();
            
            case self::SCENARIO_BOTH:
                if($this->updateDetail() && $this->updatePhoto()){
                    return true;
                }
            
            default:
                # code...
                break;
        }
        return false;
    }

    private function updateDetail(){
        try {
            if($this->user->email != $this->email){
                $cooldown = $this->checkCooldown($this->user); 
                if($cooldown !== true){
                    $str = Utility::jsonifyError("email", Yii::t('common', 'Please try to update a different email in {token} minutes', ['token' => round(2 - $cooldown)]), CustomHttpException::KEY_WAIT_FOR_COOLDOWN);
                    throw new CustomHttpException($str,CustomHttpException::UNPROCESSABLE_ENTITY );
                }else {
                    $this->user->updateAttributes(["email"=>$this->email, "email_status"=>User::EMAIL_STATUS_NOT_VERIFIED]);
                    $sendEmail = $this->user->createAndSendActiviationTokenEmail();
                    if($sendEmail) {
                        $emailTrace = SysSesTrace::makeModel($this->email);
                        $emailTrace->save();
                    }
                }
            }
            $this->userProfile->updateAttributes(["first_name"=>$this->first_name, "last_name"=>$this->last_name, "gender"=>$this->gender, "birthday"=>$this->birthday]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    private function checkCooldown($model){
        $m = SysUserToken::find()->orderBy(['created_at'=>SORT_DESC])->andWhere(['user_id'=>$model->id])->andWhere(['type'=> SysUserToken::TYPE_EMAIL_ACTIVATION])->one();
        //skip countdown if model not found
        if($m) {
            $previousCreatedAt = $m->created_at;
            $date = date_create();
            $timeDifference = (date_timestamp_get($date) - $previousCreatedAt)/60; //in mins

            if ($timeDifference >= 2){
                $m->delete();
                return true;
            } else {
                return $timeDifference;  
            } 

        } else {
            return true;
        }
    }   
    
    private function updatePhoto(){
        try {
            $photo = $this->uploadPhotos();
            $avatar_path = $photo[0]['path'];
            $avatar_base_url = $photo[0]['base_url'];
            $this->userProfile->updateAttributes(["avatar_path"=>$avatar_path, "avatar_base_url"=>$avatar_base_url]);
            return true;
        } catch (Exception $e) {
            return false;
        }   
    }

    private function uploadPhotos() {

        $uploadAction = new \trntv\filekit\actions\UploadAction("uploads",$this);
        $uploadAction->uploadPath = "media/profile-picture";
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