<?php

namespace common\models\query;

use yii\db\ActiveQuery;
use common\components\MyCustomActiveRecord;

class DealerCompanyQuery extends \yii\db\ActiveQuery
{

    public function all($db = null)
    {
        return parent::all($db);
    }

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
