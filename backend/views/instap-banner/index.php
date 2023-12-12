<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\helpers\Url;
use common\models\User; 
use common\components\MyCustomActiveRecord;

/* @var $this yii\web\View */
/* @var $searchModel common\models\search\InstapPromotionSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('backend', 'Promotional Banner');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="instap-banner-index">


    <p>
        <?= Html::a(Yii::t('backend', 'Create Promotional Banner'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn',
                'headerOptions' => ['width' => '10px']
            ],
            [
                'label' => Yii::t('backend','Region'),
                'format' => 'raw',
                'attribute' => 'region_id',
                'filter' => false,
                'headerOptions' => ['width' => '50px'],
            ],                
            [
                'attribute' => 'thumbnail',
                'format' => 'raw',
                'headerOptions' => ['width' => '120px'],
                'value' => function($model) {
                    $link = Url::to(['instap-banner/update', 'id' => $model->id]);
                    $html = "<a href=".$link.">";
                    $html .= $model->getImage("medium"). " ";
                    $html .= "</a>";
                    return $html;
                }
            ],
            [   
                'label' => Yii::t('backend','title'),
                'attribute' => 'name',
                'format' => 'raw',
                'value' => function($model) {
                    $html = "<div class='banner-holder'>";
                    $html .= "<div class='banner-title'>".$model->title ."</div>";
                    $html .=  "<div class='banner-desc'>". $model->description . "</div>";
                    $html .= "</div>";

                    return $html;
                },
                'headerOptions' => ['width' => '*'],
            ],            
            [   'attribute'=>'created_at',
                'value' => function($model) {
                    return Yii::$app->formatter->asDatetime($model->created_at);
                },  
                'filterType' => GridView::FILTER_DATE,
                'filterWidgetOptions' => [
                  'pluginOptions' => [
                    'format' => 'dd-mm-yyyy',
                    'autoclose' => true,
                    'todayHighlight' => true,
                  ]
                ],                
                'headerOptions' => ['width' => '200px'],
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
