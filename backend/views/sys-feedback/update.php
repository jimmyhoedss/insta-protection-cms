<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\SysFeedback */

$this->title = 'Update Sys Feedback: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Sys Feedbacks', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['update', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="sys-feedback-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
