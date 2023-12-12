<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\SysFcmTokenHistory */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Sys Fcm Token Histories', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sys-fcm-token-history-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'user_id',
            'token',
            'status',
            'created_at',
            'updated_at',
        ],
    ]) ?>

</div>
