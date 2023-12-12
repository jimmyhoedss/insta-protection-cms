<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "instap_insurance_category".
 *
 * @property string $id
 * @property string $description
 *
 * @property InstapPlan[] $instapPlans
 * @property InstapPlanPool[] $instapPlanPools
 * @property UserCaseRepairCentre[] $userCaseRepairCentres
 */
class InstapInsuranceCategory extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'instap_insurance_category';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'description'], 'required'],
            [['description'], 'string'],
            [['id'], 'string', 'max' => 4],
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
            'description' => Yii::t('common', 'Description'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInstapPlans()
    {
        return $this->hasMany(InstapPlan::className(), ['category' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInstapPlanPools()
    {
        return $this->hasMany(InstapPlanPool::className(), ['plan_category' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserCaseRepairCentres()
    {
        return $this->hasMany(UserCaseRepairCentre::className(), ['insurance_category_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return \common\models\query\InstapInsuranceCategoryQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\InstapInsuranceCategoryQuery(get_called_class());
    }
}
