<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\DetailView;
use common\models\DealerUser;
use common\models\User;
use common\models\DealerCompany;
use yii\helpers\Url;
use yii\web\YiiAsset;
use backend\widgets\TabMenuDealerWidget;

/* @var $this yii\web\View */
/* @var $model common\models\DealerCompany */
YiiAsset::register($this);

$this->title = $model->business_name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Companies'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
YiiAsset::register($this);
$link = Url::to(['update', 'id' => $model->id]);
$link1 = Url::to(['dealer-user/create', 'route' => "dealer-company/view", 'route_id' => $model->id]);
?>

<div class="dealer-view">

    <h4 class='sub-title'><?= Yii::t('backend','Company Details') ?></h4>
    <a class='fa fa-pencil' href=<?=$link?>>&nbsp;Edit Company</a>
    <?php echo $model->getCompanyDetailLayout();?>
    <hr>

    <ul class="nav nav-tabs">
      <li class="active"><a data-toggle="tab" href="#staff"><?=Yii::t('backend','Staff')?></a></li>
    </ul>
    <a class='fa fa-plus' href=<?=$link1?>>&nbsp;<?=Yii::t('backend','Add Staff')?></a>

     <?= GridView::widget([

            'dataProvider' => $dataProvider_DU,
            'filterModel' => $searchModel_DU,
            'summary' => '',

            'columns' => [
                ['class' => 'yii\grid\SerialColumn',
                    'headerOptions' => ['width' => '15px']
                ],
                [
                    'label' => Yii::t('backend',"Name"),
                    'format' => 'raw',
                    'attribute' => 'full_name',
                    'value' => function($model) {
                        $link = Url::to(['dealer-user/view', 'id' => $model->user_id]);
                        $html = $model->userProfile->getAvatarSmallLayout($link);
                        return $html;
                    },
                    'headerOptions' => ['width' => '250px'],
                ],
                [
                    'attribute'=>'notes',
                    'format' => 'raw',
                    'headerOptions' => ['width' => '*'],
                ],
                [
                    'label'=> Yii::t('backend',"Role"),
                    'format' => 'raw',
                    'value' => function ($model) {
                        $html = User::getRoleLayoutById($model->user_id);
                        return $html;
                    },
                    'headerOptions' => ['width' => '100px'],
                ],                
                [
                    'attribute'=>'created_at',
                    'format' => 'raw',
                    'value' => function($model) {
                        $d = Yii::$app->formatter->asDatetime($model->created_at);
                        return $d;
                    },                 
                    'headerOptions' => ['width' => '100px'],
                ],
                [
                'class' => 'yii\grid\ActionColumn',
                'template'=>'{update} {delete}',
                'headerOptions' => ['width' => '40px'],
                'buttons' => [
                    'update' => function($url, $dealer_user, $key) {  
                         return Html::a('', ['dealer-user/update', 'id' => $dealer_user->user_id, 'route' => "dealer-company/view", 'route_id' => $dealer_user->dealer->id], ['class' => 'glyphicon glyphicon-pencil']);
                        // return print_r($model->id);
                    },
                    'delete' => function($url, $dealer_user, $key) {  
                         return Html::a('', ['dealer-user/delete', 'id' => $dealer_user->user_id, 'route' => "dealer-company/view", 'route_id' => $dealer_user->dealer->id], ['class' => 'glyphicon glyphicon-trash']);
                        // return print_r($model->id);
                    }
            ]
            ],
            ],
        ]) ?>

    </div>
</div>
