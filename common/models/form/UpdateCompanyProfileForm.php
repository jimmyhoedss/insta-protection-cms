<?php
namespace common\models\form;

use yii\base\Model;
use Yii;
use yii\web\JsExpression;
use common\models\DealerCompany;
use common\models\DealerUser;

class UpdateCompanyProfileForm extends Model
{
    public $image_file;
    public $dealer;

    const SCENARIO_DETAIL = "scenario_detail";
    const SCENARIO_PHOTO = "scenario_photo";
    const SCENARIO_BOTH = "scenario_both";

    public function rules()
    {
        return [
            [['image_file'], 'required', 'on'=>[self::SCENARIO_PHOTO,self::SCENARIO_BOTH]],
            [['image_file'], 'image', 'skipOnEmpty' => false, 'extensions' => 'png, jpg, jpeg', 'maxSize'=>1024 * 1024 * 25, 'maxFiles' => 5, 'on'=>[self::SCENARIO_PHOTO,self::SCENARIO_BOTH]]
        ];
    }

    public function update(){
        $this->dealer = DealerUser::getDealerFromUserId(Yii::$app->user);

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
        
    }
    
    private function updatePhoto(){
        try {
            $photo = $this->uploadPhotos();
            $thumbnail_path = $photo[0]['path'];
            $thumbnail_base_url = $photo[0]['base_url'];
            $this->dealer->updateAttributes(["thumbnail_path"=>$thumbnail_path, "thumbnail_base_url"=>$thumbnail_base_url]);
            return true;
        } catch (Exception $e) {
            return false;
        }   
    }

    private function uploadPhotos() {

        $uploadAction = new \trntv\filekit\actions\UploadAction("uploads",$this);
        $uploadAction->uploadPath = "media/company-picture";
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