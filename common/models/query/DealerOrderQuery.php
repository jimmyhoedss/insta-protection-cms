<?php

namespace common\models\query;

use common\models\InstapPlan;

/**
 * This is the ActiveQuery class for [[\common\models\DealerOrder]].
 *
 * @see \common\models\DealerOrder
 */
class DealerOrderQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return \common\models\DealerOrder[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \common\models\DealerOrder|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    public function byCategory($category)
    {   //need to include join with instap_plan
        $this->innerJoinWith('planPool', true);
        $this->join('inner join','instap_plan','instap_plan.id = instap_plan_pool.plan_id');
        $this->andWhere(['in', 'instap_plan.category',  InstapPlan::getSubCategory($category)]);
    
        return $this;
    }

}
