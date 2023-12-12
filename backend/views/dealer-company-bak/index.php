<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use kartik\grid\GridView;
use common\components\MyCustomActiveRecord;
use common\models\User;

/* @var $this yii\web\View */
/* @var $searchModel common\models\search\DealerCompanySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('backend', 'Companies');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="dealer-index">

  

    <p>
        <?= Html::a(Yii::t('backend', 'Create Company'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn',
                'headerOptions' => ['width' => '20px'],
            ],
            /*[
                'label' => 'Region',
                'attribute'=>'region_id',
                'headerOptions' => ['width' => '20px'],
            ],*/
            [
                'label' => Yii::t('backend', 'Name'),
                'attribute'=>'business_name',
                'format' => 'raw',
                'value' => function($model) {
                    $html = $model->business_name;
                    $html .= "<div class='small'>Reg # <i>" . $model->business_registration_number . "</i></div>";
                    return $html;
                },    
                'headerOptions' => ['width' => '250px'],
            ],
            [
                'label' => Yii::t('backend','Conntact Person'),
                'attribute'=>'business_contact_person',
                'headerOptions' => ['width' => '100px'],
            ],
            [
                'label' => Yii::t('backend','Phone'),
                'attribute'=>'business_phone',
                'headerOptions' => ['width' => '120px'],
            ],
            [
                'label' => Yii::t('backend','Email'),
                'attribute'=>'business_email',
                'headerOptions' => ['width' => '100px'],
            ],
            [
                'label' => Yii::t('backend','Address'),
                'attribute'=>'business_address',
                'headerOptions' => ['width' => '*'],
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
                'template' => '{view} {update} {status} {organisation} {organisation_admin}',
                'headerOptions' => ['width' => '20px'],
                'visibleButtons' => [
                    //oh: "use" to pass in variables from the parent scope to a closure
                    'organisation_admin' => function ($model) use ($page) {
                        return (Yii::$app->authManager->checkAccess(Yii::$app->user->id, User::ROLE_ADMINISTRATOR));
                    },
                ],
                'buttons' => [
                    'status' => function ($url, $model) {
                        $html = MyCustomActiveRecord::getStatusHtml($model) . "<br>";
                        return $html;
                    },
                    'organisation' => function ($url, $model) { 
                        return Html::a('', Url::to(["dealer-company/chart", 'id'=>$model->id]), ['class' => 'fa fa-sitemap']);
                    },
                    'organisation_admin' => function ($url, $model) { 
                        return Html::a('', Url::to(["dealer-company/chart-admin", 'id'=>$model->id]), ['class' => 'fa fa-sitemap', 'title'=>"Admin View", 'style' => ['color'=>'red']]);
                    }
                ],
            ],
        ],
    ]); ?>


</div>
