<?php

namespace common\models\query;

/**
 * This is the ActiveQuery class for [[\common\models\InstapInsuranceCategory]].
 *
 * @see \common\models\InstapInsuranceCategory
 */
class InstapInsuranceCategoryQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return \common\models\InstapInsuranceCategory[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \common\models\InstapInsuranceCategory|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
