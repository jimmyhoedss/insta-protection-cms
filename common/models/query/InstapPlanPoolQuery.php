<?php

namespace common\models\query;

/**
 * This is the ActiveQuery class for [[\common\models\InstapPlanPool]].
 *
 * @see \common\models\InstapPlanPool
 */
class InstapPlanPoolQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return \common\models\InstapPlanPool[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \common\models\InstapPlanPool|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
