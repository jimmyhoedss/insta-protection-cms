<?php

use yii\helpers\Html;
// use kartik\grid\GridView;
use kartik\grid\GridView;
use yii\helpers\Url;
use backend\widgets\TabMenuPlanWidget;
use common\models\UserProfile;
use common\models\InstapPlanPool;
use common\models\UserCase;
use common\models\DealerCompany;


/* @var $this yii\web\View */
/* @var $searchModel common\models\search\InstapPlanPoolSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('backend', 'Policy Activations');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="instap-plan-pool-index">


    <?php 
        echo TabMenuPlanWidget::widget(['page'=>$page]);
    ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn',
             'headerOptions' => ['width' => '20px'],],

             // 'id',
            [
                'label'=>'User',
                'format' => 'raw',
                'attribute' => 'full_name',
                'value' => function($model) {
                    $link = Url::to(['user/view', 'id' => $model->user->id]);
                    $html = $model->userProfile->getAvatarLayout($link);
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
                    $html .= $model->plan->getPlanBanner();
                    $html .= "</a>";
                    return $html;
                },                 
                'headerOptions' => ['width' => '150px'],
            ],
            [
                'format' => 'raw',
                'attribute' => 'policy_number',
                  'value' => function($model) {  
                    $html = $model->policy_number."<br>";
                    return $html;
                },
                'headerOptions' => ['width' => '*'],
            ],
            [   
                'label'=>Yii::t('backend','Screen Crack Coverage Period'),
                'format' => 'raw',
                'attribute' => 'coverage_period',
                'filter' => '',
                'value' => function($model) { 
                    $html = $model->getCoverageLayout();
                    // $html .= "[".$model->plan->coverage_period." months]</p>";
                    return $html;
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
                'label'=>Yii::t('backend','E/W Coverage Period'),
                'format' => 'raw',
                'attribute' => 'ew_coverage_period',
                'filter' => '',
                'value' => function($model) { 
                    $html = $model->getEWCoverageLayout();
                    // $html .= "[".$model->plan->coverage_period." months]</p>";
                    return $html;
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
                'label'=>Yii::t('backend','Purchase From'),
                'format' => 'raw',
                'attribute' => 'company_name',
                //'filter' => '',
                'value' => function($model) {
                    $dealer = DealerCompany::find()->andWhere(["id"=>$model->dealer_company_id])->one();          
                    $link = Url::to(['dealer-company/view', 'id' => $model->dealer_company_id]);
                    $html = $dealer->getContactSmallLayout($link);
                    $link = Url::to(['dealer-user/view', 'id' => $model->dealerOrder->dealer_user_id]);
                    $html .= $model->dealerOrder->userProfile->getAvatarSmallLayout($link);
                    return $html;
                },                 
                'headerOptions' => ['width' => '180px'],
            ],
            [
                'label'=>Yii::t('backend','Plan Status'),
                'format' => 'raw',
                'attribute' => 'plan_status',
                'filter' => InstapPlanPool::allPlanStatus(),
                'value' => function ($model) {
                    $status = InstapPlanPool::allPlanStatus()[$model->plan_status];
                    if($model->plan_status == InstapPlanPool::STATUS_PENDING_CLAIM){
                        $status .= "<br>";
                        $status .= "<i>(" . UserCase::allCaseStatus()[$model->userCase->current_case_status] . ")</i>";
                    }

                    return $status;
                },
                'headerOptions' => ['width' => '150px'],
            ],            
            [
                'attribute'=>'created_at',
                'format' => 'raw',
                // 'filter' => false,
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
                'headerOptions' => ['width' => '100px'],
            ],
            [
                'label'=>Yii::t('backend','Last Updated At'),
                'attribute'=>'updated_at',
                // 'filter' => false,
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
                'headerOptions' => ['width' => '100px'],


            ],
            // [

            //     'attribute'=>'created_at',
            //     'value' => function ($model, $index, $widget) {
            //       return Yii::$app->formatter->asDateTime($model->created_at);
            //     },
            //     'filterType' => GridView::FILTER_DATE,
            //     'filterWidgetOptions' => [
            //       'pluginOptions' => [
            //         'format' => 'dd-mm-yyyy',
            //         'autoclose' => true,
            //         'todayHighlight' => true,
            //       ]
            //     ],

            // ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template'=>'{update}',
                'headerOptions' => ['width' => '40px'],
            ],
        ],
    ]); ?>


</div>
