<?php
namespace common\components;

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


//use together with common/widget/MyUpload for preSignedUrl

class MyUploadAction extends UploadAction {

    public function run() {
        $r = parent::run();
        // Yii::warning($r);
        $key = $r['files'][0]["path"];
        
        //loynote: for windows, need to change key to forward slash!!!
        // Utility::replacePathAccordingToOS($key);


        $path = str_replace('\\', '/', $key);

        // Yii::warning($key);
        // Yii::warning($path);

        $r['files'][0]["preSignedUrl"] = Utility::getPreSignedS3Url($path);
        //Yii::warning($r['files'][0]["preSignedUrl"]);
        return $r;   
    }


}
