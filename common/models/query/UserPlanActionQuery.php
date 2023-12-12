<?php

namespace common\models\query;
use common\components\MyCustomActiveRecord;

/**
 * This is the ActiveQuery class for [[\common\models\UserPlanAction]].
 *
 * @see \common\models\UserPlanAction
 */
class UserPlanActionQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return \common\models\UserPlanAction[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \common\models\UserPlanAction|array|null
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
