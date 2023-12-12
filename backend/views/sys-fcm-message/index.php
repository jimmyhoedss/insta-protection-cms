<?php

use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\search\SysFcmMessageSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Sys Fcm Messages';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sys-fcm-message-index">

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?php echo Html::a('Create Sys Fcm Message', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php echo GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'type',
            'to',
            'title',
            'body',
            // 'link_desc',
            // 'link_url:url',
            // 'action',
            // 'fcm_token:ntext',
            // 'created_at',
            // 'created_by',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>
