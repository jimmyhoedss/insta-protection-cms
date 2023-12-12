<?php

namespace common\models\query;

use common\components\MyCustomActiveRecordQuery;
//\yii\db\ActiveQuery
class InstapPromotionQuery extends MyCustomActiveRecordQuery
{
    public function all($db = null)
    {
        return parent::all($db);
    }

    public function one($db = null)
    {
        return parent::one($db);
    }
}
