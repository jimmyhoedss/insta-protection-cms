<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use common\behaviors\MyAuditTrailBehavior;
use common\models\QcdRetailStore;



class UserCaseRetailStore extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_case_retail_store';
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
            [['region_id', 'insurance_category_id', 'case_id', 'retail_store_id'], 'required'],
            [['case_id', 'retail_store_id', 'created_at', 'created_by'], 'integer'],
            [['region_id'], 'string', 'max' => 10],
            [['insurance_category_id'], 'string', 'max' => 4],
        ];
    }

    public function getRetailStore() {
        return $this->hasOne(QcdRetailStore::class, ['id' => 'retail_store_id']);
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
            'retail_store_id' => Yii::t('app', 'Retail Store ID'),
            'created_at' => Yii::t('app', 'Created At'),
            'created_by' => Yii::t('app', 'Created By'),
        ];
    }

    public static function makeModel($case, $insurance_category_id, $retailStore) {
        $m = new SELF();
        $m->case_id = $case->id;
        $m->insurance_category_id = $insurance_category_id;
        $m->region_id = $retailStore->country_code;
        $m->retail_store_id = $retailStore->id;
        return $m ;
    }

    public static function getRetailStoreDetails($retail_store_id) {
        $connection = Yii::$app->getDb();
        $retail_store = $connection->createCommand("SELECT * FROM  qcd_retail_store WHERE retail_store_id = '$retail_store_id'")->queryOne();
        return $retail_store;
    }
}
