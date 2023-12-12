<?php

use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\search\UserRewardSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'User Rewards';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-reward-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create User Reward', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn',
                'headerOptions' => ['width' => '30px']],
            'user_id',
            'reward_id_hash',
            'status_redeem',
            //'redeem_at',
            [
                'attribute'=>'redeem_at',
                'value' => function($model) {
                    return Yii::$app->formatter->asDatetime($model->created_at);
                },                 
                'headerOptions' => ['width' => '100px'],
            ], 
            //'status',
            //'created_at',

            ['class' => 'yii\grid\ActionColumn',
                'headerOptions' => ['width' => '50px']],
        ],
    ]); ?>
</div>
