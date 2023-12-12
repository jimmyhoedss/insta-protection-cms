<?php

namespace common\models\query;

use common\components\MyCustomActiveRecordQuery;
use common\components\MyCustomActiveRecord;

//\yii\db\ActiveQuery

class QcdRepairCentreQuery extends MyCustomActiveRecordQuery
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
    
}
