<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\DealerOrderInventoryOverview */

$this->title = Yii::t('backend', 'Allocate Stocks');
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Inventories'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="dealer-order-inventory-overview-create">


    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
