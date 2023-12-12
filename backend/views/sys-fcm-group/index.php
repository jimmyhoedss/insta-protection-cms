<?php

use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\search\SysFcmGroupSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Message Groups';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sys-fcm-group-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Message Group', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn',
                'headerOptions' => ['width' => '20px']],
            //'id',
            'name',
            [
                'attribute'=>'created_at',
                'value' => function($model) {
                    return Yii::$app->formatter->asDatetime($model->created_at);
                },                 
                'headerOptions' => ['width' => '160px'],
            ], 

            ['class' => 'yii\grid\ActionColumn',
                'headerOptions' => ['width' => '50px']],
        ],
    ]); ?>
</div>
