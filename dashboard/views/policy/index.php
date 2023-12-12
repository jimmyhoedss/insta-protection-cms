<?php

use yii\helpers\Html;
// use kartik\grid\GridView;
use kartik\grid\GridView;
use yii\helpers\Url;
use backend\widgets\TabMenuPlanWidget;
use common\models\UserProfile;
use common\models\InstapPlanPool;
use common\models\DealerCompany;
use common\models\UserCase;

$this->title = Yii::t('dashboard', 'Plans');
// $this->params['breadcrumbs'][] = $this->title;
?>
<div class="instap-plan-pool-index">

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        // 'filterModel' => $searchModel,
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn',
                'headerOptions' => ['width' => '20px'],
            ],
            [
                'label'=> Yii::t('dashboard','Plan Name'),
                'format' => 'raw',
                'attribute' => 'plan_type',
                //'filter' => '',
                'value' => function($model) {       
                    return $model->plan->getPlanBanner();
                },                 
                'headerOptions' => ['width' => '150px'],
            ],
            [
                'label'=> Yii::t('dashboard','Device info'),
                'format' => 'raw',
                'attribute' => 'plan_type',
                'filter' => false,
                'value' => function($model) {
                    $html = "<p>" .Yii::t('dashboard', 'Device')." : <b><i>".$model->userPlan->details->sp_brand."</i></b></p>";     
                    $html .= "<p>".Yii::t('dashboard', 'Model Number')." : <b><i>".$model->userPlan->details->sp_model_number."</i></b></p>";     
                    return $html;   
                },                 
                'headerOptions' => ['width' => '250px'],
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
                'label'=> Yii::t('dashboard','Coverage Period'),
                'format' => 'raw',
                'attribute' => 'coverage_period',
                //'filter' => '',
                'value' => function($model) { 
                    $html = $model->getCoverageLayout();
                    // $html .= "[".$model->plan->coverage_period." months]</p>";
                    return $html;
                },                 
                'headerOptions' => ['width' => '150px'],
            ],
            [
                'label'=> Yii::t('dashboard','Purchase From'),
                'format' => 'raw',
                'attribute' => 'dealer_company_id',
                //'filter' => '',
                'value' => function($model) {
                    $dealer = DealerCompany::find()->andWhere(["id"=>$model->dealer_company_id])->one();          
                    $html = $dealer->getContactSmallLayout();
                    return $html;
                },                 
                'headerOptions' => ['width' => '180px'],
            ],
            [
                'label'=> Yii::t('dashboard','Plan Status'),
                'format' => 'raw',
                'attribute' => 'plan_status',
                'filter' => InstapPlanPool::allPlanStatus(),
                'value' => function ($model) {
                    $status = '<p><b>'.InstapPlanPool::allPlanStatus()[$model->plan_status].'</b></p>';
                    if($model->plan_status == InstapPlanPool::STATUS_PENDING_CLAIM){
                        $case_status = $model->userCase->current_case_status;
                        // $status .= "<br>";
                        if($case_status == UserCase::CASE_STATUS_CLAIM_REQUIRE_CLARIFICATION) {
                            $status .= '<p><i class="fa fa-exclamation-triangle" style="color:red;" title="Some details are incomplete or missing. Please clarify them before re-submitting your claim."> </i><i> '. UserCase::allCaseStatus()[$case_status] . '</i></p>';
                        } else {
                            $status .= '<p><i>( '. UserCase::allCaseStatus()[$case_status] . ')</i></p>';
                        }
                    }

                    return $status;
                },
                'headerOptions' => ['width' => '250px'],
            ],            
            [
                'label' => Yii::t('dashboard', 'Created At'),
                'attribute'=> 'created_at',
                'format' => 'raw',
                'value' => function($model) {
                    $d = Yii::$app->formatter->asDatetime($model->created_at);
                    return $d;
                },                 
                'headerOptions' => ['width' => '100px'],
            ],
            [
                'label'=> Yii::t('dashboard', 'Last Updated At'),
                'attribute'=>'updated_at',
                'format' => 'raw',
                'value' => function($model) {
                    $d = Yii::$app->formatter->asDatetime($model->updated_at);
                    return $d;
                },                 
                'headerOptions' => ['width' => '100px'],
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template'=> '{claim} {clarification}',
                'headerOptions' => ['width' => '200px'],
                'buttons' => [
                    // 'attention' => function ($url, $model) {
                    //     $html = "";
                    //     if(($model->plan_status == InstapPlanPool::STATUS_PENDING_CLAIM && $model->userCase->current_case_status == UserCase::CASE_STATUS_CLAIM_REQUIRE_CLARIFICATION)){
                    //         $html = Html::tag('span', '', ['class' => 'fa fa-exclamation-triangle text-red']);
                    //     } else if($model->plan_status == InstapPlanPool::STATUS_ACTIVE){
                    //         $html = Html::tag('span', '', ['class' => 'fa fa-thumbs-up text-green']);
                    //     }
                    //     return $html;
                    // },
                    // 'view' => function ($url, $model) {
                    //     $html = Html::a('<span class="fa fa-eye"></span>', $url, ['title' => 'View Details']);
                    //     return $html;
                    // },
                    'claim' => function ($url, $model) {
                        $html = "";
                        if($model->plan_status == InstapPlanPool::STATUS_ACTIVE){
                            $html = Html::a('<span class="btn btn-primary">'.Yii::t('dashboard', 'Submit Claim').'</span>', $url, ['title' => '']);
                        }
                        return $html;
                    },
                    'clarification' => function ($url, $model) {
                        $html = "";
                        if($model->plan_status == InstapPlanPool::STATUS_PENDING_CLAIM && $model->userCase->current_case_status == UserCase::CASE_STATUS_CLAIM_REQUIRE_CLARIFICATION){
                            $html = Html::a('<span class="btn btn-danger">'.Yii::t("dashboard", "Submit Clarification").'</span>', $url, ['title' => '']);
                        }
                        return $html;
                    },
                ],
            ],
            /**/
        ],
    ]); ?>


</div>
