<?php

namespace common\models\query;

use common\components\MyCustomActiveRecordQuery;
use common\components\MyCustomActiveRecord;
use common\models\InstapPlan;

//\yii\db\ActiveQuery

class InstapPlanQuery extends MyCustomActiveRecordQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return \common\models\InstapPlan[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \common\models\InstapPlan|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    public function active()
    {
        $this->andWhere(['status' => MyCustomActiveRecord::STATUS_ENABLED]);
        return $this;
    }

    public function byCategory($category)
    {
        // print_r( InstapPlan::getSubCategory($category));exit();
        $this->andWhere(['in', 'category',  InstapPlan::getSubCategory($category)]);
       
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
