<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use common\behaviors\MyAuditTrailBehavior;
use common\components\Utility;
use api\components\CustomHttpException;

class UserCaseActionDocument extends \yii\db\ActiveRecord
{
    
    const TYPE_QUOTATION = "photo_quotation";
    const TYPE_PRE = 'photo_pre';
    const TYPE_POST = 'photo_post';
    const TYPE_INCIDENT = 'photo_incident';
    const TYPE_INCIDENT_RESUBMIT = 'photo_incident_resubmit';
    const TYPE_SERVICE_REPORT = 'service_report';
    const TYPE_INCIDENT_REPORT = 'incident_report';
    const TYPE_DISCHARGE_VOUCHER = 'discharge_voucher';

    public static function tableName()
    {
        return 'user_case_action_document';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['case_action_id', 'type'], 'required'],
            [['case_action_id', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['status','type'], 'string'],
            ['type', 'in', 'range' => array_keys(self::allDocumentType())],
            [['thumbnail_base_url', 'thumbnail_path'], 'string', 'max' => 1024],
        ];
    }
    public function behaviors() {
        return [
            "timestamp" => TimestampBehavior::className(),
            "blame" => BlameableBehavior::className(),
            "auditTrail" => MyAuditTrailBehavior::className(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'case_action_id' => Yii::t('common', 'Case Action ID'),
            'thumbnail_base_url' => Yii::t('common', 'Thumbnail Base Url'),
            'thumbnail_path' => Yii::t('common', 'Thumbnail Path'),
            'status' => Yii::t('common', 'Status'),
            'created_at' => Yii::t('common', 'Created At'),
            'created_by' => Yii::t('common', 'Created By'),
            'updated_at' => Yii::t('common', 'Updated At'),
            'updated_by' => Yii::t('common', 'Updated By'),
        ];
    }

    public static function makeModel($caseAction, $item, $type) {
        $m = new SELF();
        $m->case_action_id = $caseAction->id;
        $m->type = $type;
        $m->thumbnail_base_url = $item['base_url'];
        $m->thumbnail_path = $item['path'];
        return $m;
    }

    public static function uploadDocument($modelAction, $arr, $type) {
         //upload image
         //loop & save all photos to model
        if(!empty($arr)) {
            for ($i=0; $i < count($arr); $i++) {
                $item = $arr[$i];
                $p = UserCaseActionDocument::makeModel($modelAction, $item, $type);
                if(!$p->save()) {
                    throw CustomHttpException::internalServerError(Yii::t('common',"Cannot update case photo."));
                }                        
            }             
        }
    }

    public static function allDocumentType() {
        return [
            self::TYPE_QUOTATION => Yii::t('common','Quotation'),
            self::TYPE_PRE => Yii::t('common','Pre Photo'),
            self::TYPE_POST => Yii::t('common','Post Photo'),
            self::TYPE_INCIDENT => Yii::t('common','Incident Photo'),
            self::TYPE_INCIDENT_RESUBMIT => Yii::t('common','Incident Photo Resubmit'),
            self::TYPE_SERVICE_REPORT => Yii::t('common','Service Report'),
            self::TYPE_INCIDENT_REPORT => Yii::t('common','Incident Report'),
            self::TYPE_DISCHARGE_VOUCHER => Yii::t('common','Discharge Voucher'),
        ];
    }

    //html layout
    static public function getDocumentLayoutByModel($arr) {
        $html = "";
        $quotation_arr = [];
        $pre_arr = [];
        $post_arr = [];
        $incident_arr = [];
        $incident_resubmit_arr = [];
        $service_report_arr = [];
        $discharge_voucher_arr = [];
        $incident_report_arr = [];

        foreach ($arr as $key => $value){            
            if($value['type'] == self::TYPE_QUOTATION) {
                $d = self::documentByType($value, "pdf");
                array_push($quotation_arr, $d);
            } else if($value['type'] == self::TYPE_PRE) {
                $d1 = self::documentByType($value);
                array_push($pre_arr, $d1);
            } else if($value['type'] == self::TYPE_POST) {
                $d2 = self::documentByType($value);
                array_push($post_arr, $d2);
            } else if($value['type'] == self::TYPE_SERVICE_REPORT) {
                $d3 = self::documentByType($value, "pdf");
                array_push($service_report_arr, $d3);
            } else if($value['type'] == self::TYPE_DISCHARGE_VOUCHER) {
                $d4 = self::documentByType($value, "pdf");
                array_push($discharge_voucher_arr, $d4);
            } else if($value['type'] == self::TYPE_INCIDENT_REPORT){
                $d5 = self::documentByType($value, "pdf");
                array_push($incident_report_arr, $d5);
            } else if($value['type'] == self::TYPE_INCIDENT_RESUBMIT){
                $d6 = self::documentByType($value);
                array_push($incident_resubmit_arr, $d6);
            }else {
                $d7 = self::documentByType($value);
                array_push($incident_arr, $d7);
            }
        }

        $html .= self::loopDocument($pre_arr, "Pre photo");
        $html .= self::loopDocument($post_arr, "Post photo");
        $html .= self::loopDocument($quotation_arr, "Quotation");
        $html .= self::loopDocument($service_report_arr, "Service report");
        $html .= self::loopDocument($discharge_voucher_arr, "Discharge voucher");
        $html .= self::loopDocument($incident_arr, "Incident photo");
        $html .= self::loopDocument($incident_resubmit_arr, "Incident resubmit photo");
        $html .= self::loopDocument($incident_report_arr, "Incident Report");

       return $html;
    }


    static public function documentByType($value, $type = "img") {
        // $html = "";
        $base = $value['thumbnail_base_url'];
        //use presign url
        $path = Utility::replacePath($value['thumbnail_path']);
        $link = Utility::getPreSignedS3Url($path);
        // $link = $base . "/" . $path ;
        if($type == "img"){
            $html = "<a href=".$link." target='_blank'><img alt='$type' class='photo x-small' src='". $link."'></a> ";
        } else {
            $html = "<a href=".$link." target='_blank'><img alt='$type' class='photo xx-small' src='/img/pdf.png'> </a>";
        }

        return $html;
    }


    static public function loopDocument($arr, $doc_name) {
        $html = "";
        if(!empty($arr)) {
            $html .= '<div class="col-4"><span>'.$doc_name.'</span></div>';
            $html .= '<div class="col-8">';
            foreach ($arr as $key => $value){
                $html .= $value;
            }
            $html .= '</div>';
        }

        return $html;
    }


    
}
