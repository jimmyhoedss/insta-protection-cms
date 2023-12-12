<?php

use yii\helpers\Html;
use yii\helpers\Url;
use backend\widgets\TabMenuUserWidget;
use kartik\grid\GridView;
use common\models\User;
/* @var $this yii\web\View */
/* @var $searchModel common\models\search\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('backend', 'Users');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-view">
    <?php
        echo TabMenuUserWidget::widget(['page'=>$page]);
    ?>
    <?php 
        $url = Url::to(['user/create-ip-staff']);
        $can = Yii::$app->authManager->checkAccess(Yii::$app->user->id, User::ROLE_IP_SUPER_ADMINISTRATOR) || Yii::$app->authManager->checkAccess(Yii::$app->user->id, User::ROLE_ADMINISTRATOR);
        if($can && $page == 'ip_staff') {
            echo Html::a('<span class="fa fa-plus"> Add IP Staff</span>', $url, ['title' => 'Create IP Staff', 'class' => ' btn btn-success']); 
        }
    ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn',
                'headerOptions' => ['width' => '10px'], 
            ],
            [   
                'label'=>'Region',
                'attribute' => 'region_id',
                'filter'=>false,
                'headerOptions' => ['width' => '20px'],  
            ],    
            [
                'label' => "Name",
                'format' => 'raw',
                'attribute' => 'full_name',
                'value' => function($model) {
                    $link = Url::to(['user/view', 'id' => $model->id]);
                    $html = $model->userProfile->getAvatarSmallLayout($link);
                    return $html;
                },
                'headerOptions' => ['width' => '220px'],
            ],
            [   
                'label'=>'Mobile',
                'attribute' =>  'mobile_number',
                'value' => function($model) {
                    $html = $model->getFormatMobileNumber();
                    return $html;
                },
                'headerOptions' => ['width' => '120px'],  
            ],
            [   
                'filter' => User::mobileStatus(),
                'attribute' => 'mobile_status',
                'headerOptions' => ['width' => '70px'],  
                'visible' => ($page !== 'ip_staff'), 

            ],
            [   
                'label' => 'Email',
                'attribute' =>  'email',
                'format' => 'raw',
                'value' => function($model) use ($page) {
                    if($page == 'ip_staff') {
                        $html = "<p>Admin : " . ($model->email_admin ? $model->email_admin : '<span style="margin-left:5%;"> — </span>'). '</p>';
                        $html .= "<p>User : " . ($model->email ? $model->email : '<span style="margin-left:5%;"> — </span>'). ' </p>';
                    } else {
                        $html = "" . $model->email;
                    }
                    return $html;
                },                 
                'headerOptions' => ['width' => '200px'],  
            ],
            [   
                'label' => 'Email Status',
                'attribute' =>  'email_status',
                'filter' => User::emailStatus(),
                'visible' => ($page !== 'ip_staff'), 
                'headerOptions' => ['width' => '70px'],  
            ],
            [
                'attribute'=>'notes',
                'format' => 'raw',
                'value' => function($model) {
                    $html = "<i>" . $model->notes . "</i>";
                    return $html;
                },    
                'visible' => ($page !== 'ip_staff'), 
                'headerOptions' => ['width' => '*'],  
            ],
            [
                'header' => 'Role',
                'format' => 'raw',
                'value' => function($model) {
                    $html = User::getRoleLayoutById($model->id);
                    return $html;
                },
                'visible' => ($page == 'ip_staff'), 
                'headerOptions' => ['width' => '150px'],  
            ],
            [
                'header' => 'Permission',
                'format' => 'raw',
                'value' => function($model) {
                    $html = User::getPermissionLayoutById($model->id);
                    return $html;
                },
                'visible' => ($page == 'ip_staff'), 
                'headerOptions' => ['width' => '150px'],  
            ],
            [   
                'filter' => User::accountStatus(),
                'attribute' =>  'account_status',
                'headerOptions' => ['width' => '70px'], 
                'visible' => ($page !== 'ip_staff'), 

            ],
            [
                'attribute'=>'created_at',
                'format' => 'raw',
                'value' => function($model) {
                    $d = Yii::$app->formatter->asDatetime($model->created_at);
                    return $d;
                },     
                'visible' => ($page !== 'ip_staff'), 
                'headerOptions' => ['width' => '100px'],
            ],
            [
                'attribute'=>'login_at',
                'format' => 'raw',
                'value' => function($model) {
                    $d = Yii::$app->formatter->asDatetime($model->login_at);
                    return $d;
                },                 
                'headerOptions' => ['width' => '100px'],
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update} {update_ip_staff} {delete_ip_staff}',
                'headerOptions' => ['width' => '20px'],
                'visibleButtons' => [
                    //oh: "use" to pass in variables from the parent scope to a closure
                    'update_ip_staff' => function ($model) use ($page) {
                        return (Yii::$app->authManager->checkAccess(Yii::$app->user->id, User::ROLE_IP_SUPER_ADMINISTRATOR) || Yii::$app->authManager->checkAccess(Yii::$app->user->id, User::ROLE_ADMINISTRATOR)) && $page == 'ip_staff';
                    },
                    'delete_ip_staff' => function ($model) use ($page) {
                        return (Yii::$app->authManager->checkAccess(Yii::$app->user->id, User::ROLE_IP_SUPER_ADMINISTRATOR) || Yii::$app->authManager->checkAccess(Yii::$app->user->id, User::ROLE_ADMINISTRATOR)) && $page == 'ip_staff';
                    },
                ],
                'buttons' => [
                    'update_ip_staff' => function ($url, $model) {
                        $url = Url::to(['user/update-ip-staff', 'id' => $model->id]);
                        $html = Html::a('<span class="fa fa-user-edit""></span>', $url, ['title' => 'Update Permission']);
                        return $html;
                    },
                    'delete_ip_staff' => function ($url, $model) {
                        $url = Url::to(['user/delete-ip-staff', 'id' => $model->id]);
                        $html = Html::a('<span class="fa fa-trash""></span>', $url, ['title' => 'Remove Permission','data' => [
                            'confirm' => 'Are you sure you want to remove permission?',
                            'method' => 'post',
                        ]]);
                        return $html;
                    },
                    
                ],
            ],
        ],
    ]); ?>


</div>
