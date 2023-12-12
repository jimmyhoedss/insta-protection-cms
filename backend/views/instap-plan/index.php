<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\helpers\Url;
use common\models\User;
use common\models\InstapPlan;
use common\components\MyCustomActiveRecord;
use kartik\date\DatePicker;



/* @var $this yii\web\View */
/* @var $searchModel common\models\search\InstapPlanSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('backend', 'Plan Offerings');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="instap-plan-index">


    <p>
        <?= Html::a(Yii::t('backend', 'Create Plan'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn',
                'headerOptions' => ['width' => '20px']
            ],
            [
                'label' => Yii::t('backend','Region'),
                'format' => 'raw',
                'attribute' => 'region_id',
                'filter' => false,
                'headerOptions' => ['width' => '50px'],
            ],
            [
                'attribute' => 'Thumbnail',
                'format' => 'raw',
                'headerOptions' => ['width' => '120px'],
                'value' => function($model) {
                    $link = Url::to(['instap-plan/update', 'id' => $model->id]);
                    $html = "<a href=".$link.">";
                    $html .= $model->getImage("medium"). " ";
                    $html .= "</a>";
                    return $html;
                }
            ],
            [
                // 'label'=>Yii::t('backend','Plan Status'),
                'format' => 'raw',
                'attribute' => 'category',
                'filter' => InstapPlan::category(),
                'value' => function ($model) {
                        $status = "<i>(" . InstapPlan::allPlanCategory()[$model->category] . ")</i>";

                    return $status;
                },
                'headerOptions' => ['width' => '150px'],
            ],
            [
                // 'label'=>Yii::t('backend','Plan Status'),
                'format' => 'raw',
                'attribute' => 'tier',
                'filter' => InstapPlan::allPlanTier(),
                'value' => function ($model) {
                        $status = "<i>(" . InstapPlan::allPlanTier()[$model->tier] . ")</i>";

                    return $status;
                },
                'headerOptions' => ['width' => '150px'],
            ],
            [   
                'label' => Yii::t('backend','Name'),
                'attribute' => 'name',
                'format' => 'raw',
                'value' => function($model) {
                    $html = "<div class='plan-holder'>";
                    $html .= "<div class='plan-name'>".$model->name ."</div>";
                    $html .= "<div class='plan-desc'>". $model->description . "</div>";
                    if($model->policyPdf){
                        $html .= "<div class='' style='position:absolute; top: 0; right: 0;'>";
                        $html .= Html::a('<i class="fa fa-file"></i>', $model->policyPdf, ['class' => 'btn btn-primary', 'target'=>"_blank"]);
                        $html .= "</div>";
                    }
                    $html .= "<div class='plan-detail'>" . $model->sku . "";
                    $html .= "<br>" . $model->coverage_period . " months coverage</div>";
                    $html .= "</div>";

                    return $html;
                },
                'headerOptions' => ['width' => '*'],
            ],
            [
                'format' => 'raw',
                'label' => Yii::t('backend','Price'),
                // 'attribute' => 'retail_price',
                //'filter' => 'plan_sku',
                'value' => function($model) {
                    $symbol = $model->currencySymbol()[Yii::$app->session->get('region_id')];
                    $html = "<div class='column'>";
                    $html .= "<p>Retail price : ".$symbol." ".number_format($model->retail_price, 2)."</p>";
                    $html .= "<p>Premium price : ".$symbol." ".number_format($model->premium_price, 2)."</p>";
                    $html .= "<p>Dealer price : ".$symbol." ".number_format($model->dealer_price, 2)."</p>";
                    $html .= "</div>";

                    return $html;
                },
                'headerOptions' => ['width' => '180px'],
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
                'class' => 'yii\grid\ActionColumn',
                'template' => Yii::$app->user->can(User::ROLE_ADMINISTRATOR) ? '{status} {view} {update} ' : '{status} {view} {update}',
                'headerOptions' => Yii::$app->user->can(User::ROLE_ADMINISTRATOR) ? ['width' => '70px'] : ['width' => '20px'],
                'buttons' => [
                    'status' => function ($url, $model) {
                        $html = "" . MyCustomActiveRecord::getStatusHtml($model) . "<br>";
                        return $html;
                    },
                    // 'update' => function ($url, $model) {
                    //     $html = Html::a('<span class="glyphicon glyphicon-pencil"></span>', $url, ['title' => 'Edit']);
                    //     return $html;
                    // },
                ],
                'headerOptions' => ['width' => '10px'],
            ],
        ],
    ]); ?>


</div>
