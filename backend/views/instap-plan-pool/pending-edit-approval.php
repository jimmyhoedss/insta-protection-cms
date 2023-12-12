<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\helpers\Url;
use backend\widgets\TabMenuPlanWidget;

use common\models\UserProfile;
use common\models\InstapPlanPool;
use common\models\DealerCompany;
use common\models\User;

$this->title = Yii::t('backend', 'Policy Detail Pending Edit Approval');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="instap-plan-pool-index">


    <?php 
        echo TabMenuPlanWidget::widget(['page'=>$page]);
    ?>
    

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn',
             'headerOptions' => ['width' => '20px'],],

             // 'id',
            [
                'label'=>Yii::t('backend','User'),
                'format' => 'raw',
                'attribute' => 'full_name',
                //'filter' => '',
                'value' => function($model) {
                    //$user = UserProfile::find()->all();          
                    // $str = $model->user->userProfile->fullName ;
                    $link = Url::to(['user/view', 'id' => $model->planPool->user->id]);
                    $html = $model->planPool->user->userProfile->getAvatarLayout($link);
                    return $html;
                },                 
                'headerOptions' => ['width' => '270px'],
            ],
            // 'user_id',
            [
                'label'=>Yii::t('backend','Plan Name'),
                'format' => 'raw',
                'attribute' => 'plan_type',
                //'filter' => '',
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
                 // 'label'=>'SKU',
                'format' => 'raw',
                'attribute' => 'policy_number',
                //'filter' => 'plan_sku',
                  'value' => function($model) {  
                    $html = $model->planPool->policy_number."<br>";
                    return $html;
                },
                'headerOptions' => ['width' => '250px'],
            ],
            /*'sp_brand',
            'sp_model_number',
            'sp_model_name',
            'sp_serial',
            'sp_imei',
            'sp_color',*/
            [
                'label'=>Yii::t('backend','Details'),
                'format' => 'raw',
                  'value' => function($model) {  
                    $html = $model->sp_brand." ";
                    $html .= $model->sp_model_name." ";
                    $html .= "(" . $model->sp_model_number.")<br>";
                    $html .= $model->sp_imei."<br>";
                    $html .= $model->sp_color."<br>";
                    return $html;
                },
                'headerOptions' => ['width' => '250px'],
            ],
            [
                'label'=>Yii::t('backend','Edit notes'),
                'format' => 'raw',
                'value' => function($model) {
                    $html = "<i>" . $model->notes . "</i>";
                    return $html;
                },
            ],              
            [
                'label'=>Yii::t('backend','Edit by'),
                'format' => 'raw',
                'attribute' => 'updated_by',
                //'filter' => '',
                'value' => function($model) {
                    $user = User::find()->where(['id' => $model->updated_by])->one();          
                    $link = Url::to(['user/view', 'id' => $model->updated_by]);
                    return $user->userProfile->getAvatarSmallLayout($link);
                },                 
                'headerOptions' => ['width' => '250px'],
            ],
            [
                'attribute'=>'created_at',
                'label'=>'Date',
                'format' => 'raw',
                'value' => function($model) {
                    $d = Yii::$app->formatter->asDatetime($model->created_at);
                    return $d;
                },                 
                'headerOptions' => ['width' => '100px'],
            ],

            [
                'class' => 'yii\grid\ActionColumn',
                'template'=>'{update}',
                'buttons' => [
                    'update' => function ($url, $model) {
                        $url = Url::to(['instap-plan-pool/update', 'id'=> $model->plan_pool_id]);
                        return Html::a('<span class="glyphicon glyphicon-pencil"></span>', $url, ['title' => 'update']); 
                        },
                    ],
                'headerOptions' => ['width' => '40px'],
            ],
        ],

    ]); ?>


</div>
