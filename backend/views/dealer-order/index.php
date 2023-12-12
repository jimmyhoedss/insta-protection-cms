<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use common\models\User;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel common\models\search\DealerOrderSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('backend', 'Dealer Orders');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="dealer-order-index">


    <!-- <p>
        <?= Html::a(Yii::t('backend', 'Create Dealer Order'), ['create'], ['class' => 'btn btn-success']) ?>
    </p> -->

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

   

     <?= GridView::widget([

        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        

        'columns' => [
            ['class' => 'yii\grid\SerialColumn',
                'headerOptions' => ['width' => '15px']
            ],

             // 'id',
            // [   
            //     'label'=>'Sold by',
            //     'attribute' =>  'dealer_user_id',
            //     'value' => function($model) {
            //         $html = $model->plan_pool_id;
            //         return $html;
            //     },
            //     'headerOptions' => ['width' => '150px'],  
            // ],
            [
                'label' => "Dealer",
                'format' => 'raw',
                'attribute' => 'full_name',
                //'filter' => User::getUserProfile()->andWhere(['user_id' => '1']),
                'value' => function($model) {
                    $link = Url::to(['user/view', 'id' => $model->dealer_user_id]);
                    $html = "<a>" . $model->dealerUser->userProfile->avatarPic .$model->dealerUser->userProfile->fullName. "</a><br>" ;
                    $str = "";
                    if($model->dealerUser->id){
                        $roles = Yii::$app->authManager->getRolesByUser($model->dealerUser->id);
                        foreach ($roles as $key => $value) {
                            $str .= $key . "";
                        }
                }
                    $html .= "[".ucwords(str_replace('_', ' ', $str))."]";
                    return $html;
                },
                'headerOptions' => ['width' => '120px'],
            ],
            [
                'label'=>'Plan Name',
                'format' => 'raw',
                'attribute' => 'plan_pool_id',
                //'filter' => '',
                'value' => function($model) {       
                    $html = "";
                    // $html .= $model->planPool->plan->getImage("plan"). " ";
                    // $html .= "</a><br>";
                    $html .= "<p><b/>".$model->planPool->plan->name ."</b></p>";
                    return $html;
                },                 
                'headerOptions' => ['width' => '150px'],
            ],
            [
                'attribute'=>'price',
                'format' => 'raw',
                'headerOptions' => ['width' => '80px'],
            ],
            [
                'attribute'=>'notes',
                'format' => 'raw',
                'headerOptions' => ['width' => '250px'],
            ],
            [
                'attribute'=>'customer',
                'format' => 'raw',
                'value' => function($model) {
                    $c = User::find()->where(["id" => $model->created_by])->one();
                    $link = Url::to(['user/view', 'id' => $model->created_by]);
                    $html = "<a href='".$link."'>" . $c->userProfile->avatarPic .$c->userProfile->fullName. "</a><br>" ;
                    return $html;
                },                 
                'headerOptions' => ['width' => '80px'],
            ],
            //'created_by',
            //'updated_at',
            //'updated_by',
            [
                'attribute'=>'created_at',
                'format' => 'raw',
                'value' => function($model) {
                    $d = Yii::$app->formatter->asDatetime($model->created_at);
                    return $d;
                },                 
                'headerOptions' => ['width' => '80px'],
            ],

            /*[   
                'class' => 'yii\grid\ActionColumn', 
                'headerOptions' => ['width' => '20px']
            ],*/
        ],
    ]); ?>


</div>
