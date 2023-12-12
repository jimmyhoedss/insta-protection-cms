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


class MyCustomActiveRecord extends \yii\db\ActiveRecord {
    const STATUS_ENABLED = "enabled";
    const STATUS_DISABLED = "disabled";
    const STATUS_TRUE = "true";
    const STATUS_FALSE = "false";

    /*
    //loyhack
    //yii\base\Model, need to covert _error to public variable, for adding message_key
    //private $_errors;
    public $_errors;
    */

    public $thumbnail;
    public $logo_thumbnail;
    public $rare_thumbnail;
    public $pdf;

    // public $quotation;
    // public $photo_pre;
    // public $photo_post;
    // public $service_report;
    // public $discharge_voucher;

    public function init() {
        if(property_exists($this,'status') && !method_exists($this,'search')) {
            $this->status = MyCustomActiveRecord::STATUS_ENABLED;
        }
        parent::init();

    }
    
    public function behaviors()
    {
        return [
            "timestamp" => TimestampBehavior::className(),
            "blame" => BlameableBehavior::className(),
            "auditTrail" => MyAuditTrailBehavior::className(),           
            "upload" =>
            [
                'class' => UploadBehavior::className(),
                'attribute' => 'thumbnail',
                'pathAttribute' => 'thumbnail_path',
                'baseUrlAttribute' => 'thumbnail_base_url'
            ], 
            "uploadLogo" =>
            [
                'class' => UploadBehavior::className(),
                'attribute' => 'logo_thumbnail',
                'pathAttribute' => 'logo_thumbnail_path',
                'baseUrlAttribute' => 'logo_thumbnail_base_url'
            ],
            "upload_pdf" =>
            [
                'class' => UploadBehavior::className(),
                'attribute' => 'pdf',
                'pathAttribute' => 'pdf_path',
                'baseUrlAttribute' => 'pdf_base_url'
            ],
        ];
    }
    public function rules()
    {
        return [
            [['thumbnail', 'logo_thumbnail', 'rare_thumbnail', 'pdf'], 'safe'],//important for upload!!
        ];
    }

    public static function find()
    {
        return new MyCustomActiveRecordQuery(get_called_class());
    }

    public static function statuses()
    {
        return [
            self::STATUS_ENABLED => Yii::t('common', 'Enabled'),
            self::STATUS_DISABLED => Yii::t('common', 'Disabled')
        ];
    }

    public static function boolean()
    {
        return [
            "true" => Yii::t('common', 'True'),
            "false" => Yii::t('common', 'False')
        ];
    }
  
    public function getImage($style = "", $type = "default", $as_url = false) {
        $base = "";
        $path = "";
        $css = $style == "" ? "photo" : "photo " . $style;
        if ($type == "default") {
            $base = $this->thumbnail_base_url;
            $path = $this->thumbnail_path;
        } else if ($type == "logo") {
            $base = $this->logo_thumbnail_base_url;
            $path = $this->logo_thumbnail_path;
        } else if ($type == "rare") {
            $base = $this->rare_thumbnail_base_url;
            $path = $this->rare_thumbnail_path;
        }
        $link = Utility::getPreSignedS3Url($path);
        if ($as_url) {
            $html = "". $link ."";
        } else {
            $html = "<img class='".$css."' src='". $link ."'>";
        }
        return $html;
    }
    
    public function getPreviewImage($style = "", $type = "default") {
        $base = "";
        $path = "";
        $css = $style == "" ? "photo" : "photo " . $style;
        if ($type == "default") {
            $base = $this->thumbnail_base_url;
            $path = $this->thumbnail_path;
            $link = Utility::getPreSignedS3Url($path);
            $html = "<img src='". $link ."'width=540px height=228px>";
        } else if ($type == "logo") {
            $base = $this->logo_thumbnail_base_url;
            $path = $this->logo_thumbnail_path;
            $link = Utility::getPreSignedS3Url($path);
            $html = "<img src='". $link ."'width=104px height=104px style='margin-top:-52px'>";
        } 
        return $html;
    }

    static public function getStatusHtml($model) {
        $m = $model;
        $html = "";
        if ($m->status == MyCustomActiveRecord::STATUS_DISABLED) {
            $html = "<i class='text-danger fas fa-circle'></i>";
        } else if ($m->status == MyCustomActiveRecord::STATUS_ENABLED) {
            $html = "<i class='text-success fas fa-circle'></i>";
        } 
        return $html;
    }
    static public function getUserStatusHtml($model) {
        $m = $model;
        if ($m->account_status == $m::ACCOUNT_STATUS_SUSPENDED || $m->status == MyCustomActiveRecord::STATUS_DISABLED) {
            $html = "<i class='text-danger fas fa-circle'></i>";
        } else if ($m->status == MyCustomActiveRecord::STATUS_ENABLED) {
            $html = "<i class='text-success fas fa-circle'></i>";
        } 
        return $html;
    }
    static public function toObjectArray($models) {
        $d = [];
        foreach ($models as $m) {
            $o = $m->toObject();
            $d[] = $o;
        }
        return $d;
    }



}