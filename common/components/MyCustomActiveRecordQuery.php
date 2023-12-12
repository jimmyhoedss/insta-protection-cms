<?php

namespace common\components;

use yii\db\ActiveQuery;
use common\components\MyCustomActiveRecord;
/**
 * Class UserTokenQuery
 * @package common\models\query
 * @author Loy
 */
class MyCustomActiveRecordQuery extends ActiveQuery
{
    public function active()
    {
        $this->andWhere(['status' => MyCustomActiveRecord::STATUS_ENABLED]);
        return $this;
    }

    public function today()
    {
        $this->andWhere(['>=', 'created_at', strtotime('today midnight')]);
        $this->andWhere(['<', 'created_at', strtotime('tomorrow midnight')]);
        return $this;
    }
}