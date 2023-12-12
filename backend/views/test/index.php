<?php

use common\grid\EnumColumn;
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\web\JsExpression;


$this->title = Yii::t('backend', 'Test');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-index">

   <?php
        $html = "";
        $html .= "<a href='test-email'>email</a>";
        $html .= "<br>";
        $html .= "<a href='test-sms'>sms</a>";
        echo $html;
    ?>

</div>
