<?php
namespace common\widgets\MyUpload;

use Yii;
use \yii\db\Expression;
use trntv\filekit\behaviors\UploadBehavior;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use common\components\MyCustomActiveRecordQuery;
use common\components\Utility;
use common\behaviors\MyLatlngPickerBehavior;
use common\behaviors\MyAuditTrailBehavior;
use yii\helpers\ArrayHelper;
use trntv\filekit\actions\UploadAction;
use trntv\filekit\widget\Upload;
use common\widgets\MyUpload\MyUploadAsset;

use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;


class MyUpload extends Upload {

    public function init()
    {
        parent::init();
        //set initial preview image into presigned url
        //oh: ONLY FOR single file preview , multiple file can only be preview while uploading.
        if($this->value != null) {
            if ($this->multiple) {
                $key = $this->clientOptions["files"]['path'];
                $this->clientOptions["files"]['preSignedUrl'] = Utility::getPreSignedS3Url($key);
            } else {
                $key = $this->clientOptions["files"][0]['path'];
                $this->clientOptions["files"][0]['preSignedUrl'] = Utility::getPreSignedS3Url($key);            
            }
            
        }

        //Yii::warning($this->clientOptions["files"]);


    }

	public function registerClientScript()
    {
        MyUploadAsset::register($this->getView());
        $options = Json::encode($this->clientOptions);
        if ($this->sortable) {
            JuiAsset::register($this->getView());
        }
        $this->getView()->registerJs("jQuery('#{$this->getId()}').MyUploadKit({$options});");
    }

}