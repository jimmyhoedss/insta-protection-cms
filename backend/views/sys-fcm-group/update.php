<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\SysFcmGroup */

$this->title = 'Update Message Group: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Message Groups', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="sys-fcm-group-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
