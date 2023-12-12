<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\helpers\Url;
use common\models\User;
use common\models\UserCase;
use backend\widgets\TabMenuCaseWidget;


/* @var $this yii\web\View */
/* @var $searchModel common\models\search\UserCaseSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('backend', 'Policy Claims');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-case-index">

    <?php 
        echo TabMenuCaseWidget::widget(['page'=>$page]);
    ?>

    <?php echo GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn',
                'headerOptions' => ['width' => '20px'], 
            ],
            [
                'label'=>'Claim Number',
                'format' => 'raw',
                'attribute' => 'id',
                'value' => function($model) {       
                    $url = Url::to(['update', 'id' => $model->id]);
                    $html = "<a href='".$url."''>";
                    $html .= "claim #" . UserCase::formUpClaimNumber($model);  
                    $html .= "</a>";
                    return $html;
                },                 
                'headerOptions' => ['width' => '100px'],
            ],  
            [
                'label'=>'Plan',
                'format' => 'raw',
                // 'attribute' => 'id',
                'value' => function($model) {       
                    $url = Url::to(['update', 'id' => $model->id]);
                    $html = "<a href='".$url."''>";
                    $html .= $model->planPool->plan->getPlanBanner();
                    $html .= "</a>";
                    return $html;
                },                 
                'headerOptions' => ['width' => '150px'],
            ],            
            [
                'label' => "User",
                'format' => 'raw',
                'attribute' => 'full_name',
                'value' => function($model) { 
                    $link = Url::to(['user/view', 'id' => $model->id]);
                    $html = $model->userProfile->getAvatarLayout($link);
                    return $html;
                },
                'headerOptions' => ['width' => '270px'],
            ],
            [
                'attribute'=>'description',
                'format' => 'raw',
                'value' => function($model) {
                    $html = "<i>" . $model->description . "</i>";
                    return $html;
                },                 
                'headerOptions' => ['width' => '*'],  
            ],
            [
                'label'=>'Claim Status',
                'format' => 'raw',
                'attribute' => 'current_case_status',
                'filter' => UserCase::allCaseStatus(),
                'value' => function ($model) {
                    return UserCase::allCaseStatus()[$model->current_case_status];
                },
                'headerOptions' => ['width' => '150px'],
            ],
            [
                'attribute'=>'created_at',
                'format' => 'raw',
                'value' => function($model) {
                    $d = Yii::$app->formatter->asDatetime($model->created_at);
                    return $d;
                },     
                'filterType' => GridView::FILTER_DATE,
                'filterWidgetOptions' => [
                  'pluginOptions' => [
                    'format' => 'dd-mm-yyyy',
                    'autoclose' => true,
                    'todayHighlight' => true,
                  ]
                ],            
                'headerOptions' => ['width' => '150px'],
            ],
            [
                'label'=>'Last Updated At',
                'attribute'=>'updated_at',
                'format' => 'raw',
                'value' => function($model) {
                    $d = Yii::$app->formatter->asDatetime($model->updated_at);
                    return $d;
                },   
                'filterType' => GridView::FILTER_DATE,
                'filterWidgetOptions' => [
                  'pluginOptions' => [
                    'format' => 'dd-mm-yyyy',
                    'autoclose' => true,
                    'todayHighlight' => true,
                  ]
                ],              
                'headerOptions' => ['width' => '150px'],
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template'=> '{update}', //{delete}
                'headerOptions' => ['width' => '10px'],
            ],
        ],
    ]); ?>

</div>
