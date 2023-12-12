<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\grid\GridView;
use backend\widgets\TabMenuIpStaffWidget;
use common\models\User;
/* @var $this yii\web\View */
/* @var $searchModel common\models\search\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('backend', 'InstaProtection Staffs');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-view">
    <p>
        <?= Html::a(Yii::t('backend', 'Add CMS User'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn',
                'headerOptions' => ['width' => '20px'], 
            ],
            [   
                'label'=>'Region',
                'attribute' => 'region_id',
                'headerOptions' => ['width' => '60px'],  
            ],            
            [
                'label' => "Name",
                'format' => 'raw',
                'attribute' => 'full_name',
                'value' => function($model) {
                    $link = Url::to(['user/view', 'id' => $model->id]);
                    $html = $model->userProfile->getAvatarLayout($link);
                    return $html;
                },
                'headerOptions' => ['width' => '250px'],
            ],
            [
                'label' => "Notes",
                'format' => 'raw',
                'attribute' => 'Notes',
                'value' => function($model) {
                    $html = $model->notes . "";
                    return $html;
                },
                'headerOptions' => ['width' => '*'],
            ],
            [
                'header' => 'Role',
                'format' => 'raw',
                'value' => function($model) {
                    $html = User::getRoleLayoutById($model->id);
                    return $html;
                },
                'headerOptions' => ['width' => '150px'],  
            ],
            [
                'header' => 'Permission',
                'format' => 'raw',
                'value' => function($model) {
                    $html = User::getPermissionLayoutById($model->id);
                    return $html;
                },
                'headerOptions' => ['width' => '150px'],  
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
                'template' => '{update}',
                'headerOptions' => ['width' => '20px'],
                'buttons' => [

                    'update' => function ($url, $model) {
                        $html = Html::a('<span class="fa fa-user-cog""></span>', $url, ['title' => 'Update Permission']);
                        return $html;
                    },
                    
                ],
            ],
        ],
    ]); ?>

</div>


<?php

/*

                    'view' => function ($url, $model) {
                        $url = Url::to(["user/view",'id'=>$model->id]);
                        $html = Html::a('<span class="glyphicon glyphicon-eye-open""></span>', $url, ['title' => 'View']);
                        return $html;
                    },


'logout' => function ($url, $model) {
    if(Yii::$app->authManager->checkAccess(Yii::$app->user->id, Yii::$app->user->identity::ROLE_ADMINISTRATOR)){
        $html = Html::a('<span class="fa fa-sign-out-alt""></span>',
            ['/site/force-logout'],
            [
                'title' => Yii::t('backend', 'Force logout {user}', ["user"=> $model->userProfile->fullName == "" ? "+(".$model->mobile_calling_code. ")" .$model->mobile_number : $model->userProfile->fullName]),
                'data' => [
                    'confirm' => Yii::t('backend', 'Force logout {user}?', ["user"=> $model->userProfile->fullName == "" ? "+(".$model->mobile_calling_code. ")" .$model->mobile_number : $model->userProfile->fullName]),
                    'method' => 'post',
                    'params' => [
                        '_get'=> $_GET,
                        'controller'=>Yii::$app->controller->id,
                        'action'=>Yii::$app->controller->action->id,
                        'target' => \backend\controllers\SiteController::FORCE_LOGOUT_TARGET_INDIVIDUAL,
                        'user_id'=>$model->id
                    ],
                ],
            ]
        );
        return $html;
    }
    return "";
},
*/

?>