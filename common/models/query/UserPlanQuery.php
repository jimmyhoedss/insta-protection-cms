<?php

namespace common\models\query;

use common\components\MyCustomActiveRecordQuery;
use common\models\InstapPlan;
//\yii\db\ActiveQuery
class UserPlanQuery extends MyCustomActiveRecordQuery
{
    public function all($db = null)
    {
        return parent::all($db);
    }

    public function one($db = null)
    {
        return parent::one($db);
    }

    public function pass()
    {
        return $this;
    }

    public function planStatusInArr($status_type_arr)
    {
    	$this->joinWith('planPool')->where(['in','instap_plan_pool.plan_status', $status_type_arr]);
        return $this;
    }
    public function byCategory($category)
    {   //need to include join with instap_plan
        $this->join('inner join','instap_plan','instap_plan.id = instap_plan_pool.plan_id');
        $this->andWhere(['in', 'instap_plan.category',  InstapPlan::getSubCategory($category)]);
    
        return $this;
    }

    public function byTier($tier)
    {
        if($tier == InstapPlan::ALL_TIER) {
            //not doing filtering
            return $this;
        } else {
            $this->andWhere(['tier' => $tier]);
        }
        return $this;
    }


}
