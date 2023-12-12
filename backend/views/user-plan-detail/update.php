<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\UserPlanDetail */

$this->title = Yii::t('backend', 'Edit User Plan Detail: {name}', [
    'name' => $model->id,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'User Plan Details'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('backend', 'Update');
?>
<div class="user-plan-detail-update">


    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
