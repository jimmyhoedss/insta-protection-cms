<?php

namespace common\models\query;

/**
 * This is the ActiveQuery class for [[\common\models\SysSesTrace]].
 *
 * @see \common\models\SysSesTrace
 */
class SysSesTraceQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return \common\models\SysSesTrace[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \common\models\SysSesTrace|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
