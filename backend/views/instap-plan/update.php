<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\InstapPlan */

$this->title = Yii::t('backend', 'Update Plan: {name}', [
    'name' => $model->name,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Plan Offerings'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('backend', 'Update');
?>
<div class="instap-plan-update">


    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
