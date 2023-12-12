<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $model common\models\SysFcmGroup */
$this->title = "Message Group ID: " . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Sys Fcm Groups', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->name;
?>
<div class="sys-fcm-group-view">

    <h1><?= Html::encode($model->name) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Send Group Push Message', ['sys-fcm-message/group'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name',
            [
                'attribute'=>'created_at',
                'value' => function($model) {
                    return Yii::$app->formatter->asDatetime($model->created_at);
                },                 
                'headerOptions' => ['width' => '160px'],
            ], 
        ],
    ]) ?>

    <br>
    <h4>Group users</h4>
    <?= Html::a('Add User to Message Group', ['user/index'], ['class' => 'btn btn-success']) ?>
    <p class="text-muted middle"><i>
            Select the user you want to add to the group, and click on the "tag" icon to add into a group<br>

    </i></p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn', 
                'headerOptions' => ['width' => '20px']
            ],
            [
                'header' => 'User',
                'format' => 'raw',
                'value' => function($model) {
                    $html = $model->user->userProfile->avatarBlock;
                    return $html;
                },
                'headerOptions' => ['width' => '700px'],
            ],
            [
                'attribute'=>'created_at',
                'value' => function($model) {
                    return Yii::$app->formatter->asDatetime($model->created_at);
                },                 
                'headerOptions' => ['width' => '160px'],
            ], 
            [
                'class' => 'yii\grid\ActionColumn',
                'template'=>'{delete}',
                'headerOptions' => ['width' => '20px'],
                'buttons' => [
                    'delete' => function ($url, $model) {
                        $url = Url::to(['sys-fcm-group-user/delete', 'id' => $model->id , 'fcm_group_id' => $model->fcm_group_id]);
                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, ['title' => 'delete']);  
                    }
                ],
            ],
        ],
    ]); ?>

</div>
