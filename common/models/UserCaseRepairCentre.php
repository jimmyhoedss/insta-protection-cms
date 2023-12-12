<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use common\behaviors\MyAuditTrailBehavior;
use common\models\QcdRepairCentre;



class UserCaseRepairCentre extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_case_repair_centre';
    }

    public function behaviors()
    {
        return [
            'timestamp'  => [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                ],
            ],
            'blame'  => [
                'class' => BlameableBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_by'],
                ],
            ],
            "auditTrail" => MyAuditTrailBehavior::className(),
        ];
    }


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['region_id', 'insurance_category_id', 'case_id', 'repair_centre_id'], 'required'],
            [['case_id', 'repair_centre_id', 'created_at', 'created_by'], 'integer'],
            [['region_id'], 'string', 'max' => 10],
            [['insurance_category_id'], 'string', 'max' => 4],
        ];
    }

    public function getRepairCentre() {
        return $this->hasOne(QcdRepairCentre::class, ['id' => 'repair_centre_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'region_id' => Yii::t('app', 'Region ID'),
            'insurance_category_id' => Yii::t('app', 'Insurance Category ID'),
            'case_id' => Yii::t('app', 'Case ID'),
            'repair_centre_id' => Yii::t('app', 'Repair Centre ID'),
            'created_at' => Yii::t('app', 'Created At'),
            'created_by' => Yii::t('app', 'Created By'),
        ];
    }

    public static function makeModel($case, $insurance_category_id, $repairCentre) {
        $m = new SELF();
        $m->case_id = $case->id;
        $m->insurance_category_id = $insurance_category_id;
        $m->region_id = $repairCentre->country_code;
        $m->repair_centre_id = $repairCentre->id;
        return $m ;
    }

    public static function getRepairCentreDetails($repair_centre_id) {
        $connection = Yii::$app->getDb();
        $repair_centre = $connection->createCommand("SELECT * FROM  qcd_repair_centre WHERE repair_centre_id = '$repair_centre_id'")->queryOne();

        // print_r($repair_centre);
        // exit(); 

        return $repair_centre;
    }
}
