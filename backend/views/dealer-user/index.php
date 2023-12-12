<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use common\models\User;
use common\models\UserProfile;
use common\models\DealerCompany;

/* @var $this yii\web\View */
/* @var $searchModel common\models\search\DealerUserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('backend', 'Staffs');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="dealer-user-index">



    <p>
        <?= Html::a(Yii::t('backend', 'Add Staff'), ['create'], ['class' => 'btn btn-success']) ?>
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
                'label'=>Yii::t('backend','User'),
                'format' => 'raw',
                'attribute' => 'full_name',
                'value' => function($model) {
                    $link = Url::to(['user/view', 'id' => $model->user->id]);
                    $str = $model->user->userProfile->getAvatarSmallLayout($link);
                    return $str;
                },                 
                'headerOptions' => ['width' => '250px'],
            ],
            [
                'label'=>Yii::t('backend','Mobile'),
                'format' => 'raw',
                'attribute' => 'mobile_number',
                'value' => function($model) {
                    $str = $model->user->mobile_number_full;
                    return $str;
                },                 
                'headerOptions' => ['width' => '120px'],
            ],
            [
                'label'=>Yii::t('backend','Company'),
                'format' => 'raw',
                'attribute' => 'dealer',
                'value' => function ($model) {
                    $link = Url::to(['dealer-company/view', 'id' => $model->dealer_company_id]);
                    $html = $model->dealer->getContactSmallLayout($link);
                    return $html;
                },
                'headerOptions' => ['width' => '250px'],
            ],
            [
                'attribute'=>'notes',
                'format' => 'raw',
                'value' => function($model) {
                     return $model->notes . "";
                },                 
                'headerOptions' => ['width' => '*'],
            ],
            [
                'label'=>Yii::t('backend','Role'),
                'format' => 'raw',
                'attribute' => 'dealer_company_id',
                'filter' => "",
                'value' => function ($model) {
                    $html = User::getRoleLayoutById($model->user_id);
                    return $html;
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
                'headerOptions' => ['width' => '100px'],
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update} {delete}',
                'headerOptions' => ['width' => '20px'],
            ],
        ],
    ]); ?>


</div>
