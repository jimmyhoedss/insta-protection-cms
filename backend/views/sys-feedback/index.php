<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use common\models\User;
use common\models\SysFeedback;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel common\models\search\SysFeedbackSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Feedback';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sys-feedback-index">

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'layout' => "{summary}\n{pager}\n{items}\n{pager}",
        'columns' => [
            ['class' => 'yii\grid\SerialColumn', 'headerOptions' => ['width' => '30px']],
            [
                'attribute' => 'email',
                'format' => 'raw',
                'value' => function($model) {
                    $html = "" . $model->name . "";
                    $html .= "<div><a href='mailto:".$model->email."'>" . $model->email . "</a></div>";
                    $user = User::findByLogin($model->email);
                    if ($user) {
                        $url = Url::to(['user/view', 'id' => $user->id]);
                        $html .= "<a href=$url><i class='fa fa-user'></i></a>";
                    }


                    return $html;
                },
                'headerOptions' => ['width' => '250px'],
            ],
            [   
                'attribute' => 'subject',
                'filter'=> SysFeedback::subjects(),
                'format' => 'raw',
                'value' => function($model) {
                    $html = ucwords($model->subject);
                    return $html;
                },
                'headerOptions' => ['width' => '80px'],
            ],
            [   
                'attribute' => 'message',
                'format' => 'raw',
                'value' => function($model) {
                    $html = "<p><i>" . $model->message . "</i></p>";
                    return $html;
                },
            ],
            [   
                'attribute' => 'notes',
                'headerOptions' => ['width' => '200px'],
            ],            
            [
                'attribute'=>'created_at',
                'value' => function($model) {
                    return Yii::$app->formatter->asDatetime($model->created_at);
                },                 
                'headerOptions' => ['width' => '100px'],
            ], 
            [
                'class' => 'yii\grid\ActionColumn',
                'template'=>'{update} {delete}',
                'headerOptions' => ['width' => '20px'],
            ],
                
        ],
    ]); ?>
</div>
