<?php
namespace common\grid;

use yii\grid\ActionColumn;

class CustomActionColumn extends ActionColumn
{
    public function init()
    {
        parent::init();
        $this->header = "Action";
        $this->template = '{view} {update} {delete}';
    }
}
