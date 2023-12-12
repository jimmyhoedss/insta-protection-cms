<?php

namespace common\models\query;
/**
 * This is the ActiveQuery class for [[SysFcmMessage]].
 *
 * @see SysFcmMessage
 */
class SysFcmMessageQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return SysFcmMessage[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return SysFcmMessage|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
