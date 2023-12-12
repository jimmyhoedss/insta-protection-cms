<?php

namespace common\models\query;

/**
 * This is the ActiveQuery class for [[\common\models\DealerOrderAdHoc]].
 *
 * @see \common\models\DealerOrderAdHoc
 */
class DealerCompanyDealerQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return \common\models\DealerOrderAdHoc[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \common\models\DealerOrderAdHoc|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
