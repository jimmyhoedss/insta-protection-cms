<?php
namespace common\models\form;

use yii\base\Model;
use Yii;
use yii\web\JsExpression;
use trntv\filekit\behaviors\UploadBehavior;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;


class ClaimActionForm extends Model
{
    const SCENARIO_CLAIM_CLOSE = "scenario_claim_close";

    public $quotation;
    public $photo_pre;
    public $photo_post;
    public $service_report;
    public $discharge_voucher;
    public $flag_skip_doc;

    public function rules()
    {
        return [            
            [['quotation', 'photo_pre', 'photo_post', 'service_report', 'discharge_voucher', 'flag_skip_doc'], 'safe'],      
            [['quotation', 'photo_pre', 'photo_post', 'service_report', 'discharge_voucher'], 'required' , 'on' => SELF::SCENARIO_CLAIM_CLOSE],
        ];
    }

    // public function behaviors()
    // {
    //     return [          
    //         "upload" =>
    //         [
    //             'class' => UploadBehavior::className(),
    //             'attribute' => 'thumbnail',
    //             'pathAttribute' => 'thumbnail_path',
    //             'baseUrlAttribute' => 'thumbnail_base_url'
    //         ], 
    //     ];
    // }
    
 
    public function attributeLabels()
    {
        return [
            'quotation' =>Yii::t('common', 'Quotation'),
            'photo_pre' =>Yii::t('common', 'Photo Pre'),
            'photo_post' =>Yii::t('common', 'Photo Post'),
            'service_report' =>Yii::t('common', 'Service Report'),
            'discharge_voucher' =>Yii::t('common', 'Discharge Voucher'),
        ];
    }
}
