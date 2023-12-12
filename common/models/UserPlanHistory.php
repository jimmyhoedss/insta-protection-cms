<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user_plan_history".
 *
 * @property int $id
 * @property int $user_plan_id
 * @property string $purchase_channel
 * @property string $purchase_type
 * @property int $purchased_at
 * @property string $purchased_region
 * @property string $purchased_currency
 * @property int $purchased_price
 * @property int $coverage_start_at
 * @property int $coverage_end_at
 * @property string $notes
 * @property string $status
 * @property int $created_at
 * @property int $created_by
 * @property int $updated_at
 * @property int $updated_by
 */
class UserPlanHistory extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_plan_history';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'user_plan_id', 'purchase_type', 'coverage_start_at', 'coverage_end_at'], 'required'],
            [['id', 'user_plan_id', 'purchased_at', 'purchased_price', 'coverage_start_at', 'coverage_end_at', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['purchase_channel', 'purchase_type', 'notes', 'status'], 'string'],
            [['purchased_region', 'purchased_currency'], 'string', 'max' => 64],
            [['id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'user_plan_id' => Yii::t('common', 'User Plan ID'),
            'purchase_channel' => Yii::t('common', 'Purchase Channel'),
            'purchase_type' => Yii::t('common', 'Purchase Type'),
            'purchased_at' => Yii::t('common', 'Purchased At'),
            'purchased_region' => Yii::t('common', 'Purchased Region'),
            'purchased_currency' => Yii::t('common', 'Purchased Currency'),
            'purchased_price' => Yii::t('common', 'Purchased Price'),
            'coverage_start_at' => Yii::t('common', 'Coverage Start At'),
            'coverage_end_at' => Yii::t('common', 'Coverage End At'),
            'notes' => Yii::t('common', 'Notes'),
            'status' => Yii::t('common', 'Status'),
            'created_at' => Yii::t('common', 'Created At'),
            'created_by' => Yii::t('common', 'Created By'),
            'updated_at' => Yii::t('common', 'Updated At'),
            'updated_by' => Yii::t('common', 'Updated By'),
        ];
    }
}
