<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\DealerOrder */

$this->title = Yii::t('backend', 'Update Dealer Order: {name}', [
    'name' => $model->id,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Dealer Orders'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('backend', 'Update');
?>
<div class="dealer-order-update">


    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
