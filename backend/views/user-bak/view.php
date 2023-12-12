<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;
use yii\helpers\Url;
use common\models\User;
use common\models\UserProfile;
use common\models\DealerCompany;
use common\models\InstapPlanPool;
use yii\helpers\ArrayHelper;
use common\components\MyCustomActiveRecord;

/* @var $this yii\web\View */
/* @var $model common\models\User */

$this->title = Yii::t('backend', 'User Detail');
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Users'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="user-view">
    <?php echo $model->userProfile->getUserDetailLayout(); ?>
   <!--  <div class="col" style="display: flex; align-items: center;">
        <h5>Roles : </h5><?=User::getRoleLayoutById($model->id)?>
    </div>
    <div class="col" style="display: flex; align-items: center;">
        <h5>Permission : </h5><?=User::getPermissionLayoutById($model->id)?>
        
    </div> -->
    <div><a id="view-more" style="cursor: pointer;">View more <i class="fas fa-caret-down"></i></a></div>
    <div class="role-details" style="display:none;">
        <?= DetailView::widget([
            'model' => $model,
            'options' => ['class' => 'table table-striped table-bordered detail-view'],
            'attributes' => [
                [
                    'label' => Yii::t('common', 'Roles'),
                    'format' => 'raw',
                    'headerOptions' => ['width' => '*'],
                    'value' => function($model) {
                        return User::getRoleLayoutById($model->id);
                    }
                ],
                [
                    'label' => Yii::t('common', 'Permission'),
                    'format' => 'raw',
                    'headerOptions' => ['width' => '*'],
                    'value' => function($model) {
                        return User::getPermissionLayoutById($model->id);
                    }
                ],
            ],
        ]) ?>
    </div>
    <hr>
    <h4 class="sub-title"><?=Yii::t('backend', 'Current Plans')?></h4>

    <?= GridView::widget([
         'dataProvider' => $dataProvider,
         'filterModel' => $searchModel,

        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'label'=>'Plan Name',
                'format' => 'raw',
                'attribute' => 'plan_type',
                //'filter' => '',
                'value' => function($model) {       
                    $url = Url::to(['instap-plan-pool/update', 'id' => $model->id]);
                    $html = "<a href='".$url."''>";
                    $html .= $model->plan->getPlanBanner();
                    $html .= "</a>";
                    return $html;
                },                 
                'headerOptions' => ['width' => '150px'],
            ],            
            [
                'format' => 'raw',
                'attribute' => 'policy_number',
                //'filter' => 'plan_sku',
                  'value' => function($model) {  
                    $html = $model->policy_number."<br>";
                    return $html;
                },
                'headerOptions' => ['width' => '*'],
            ],
            [   
                'label'=>'Coverage Period',
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
                'label'=>'Purchase from',
                'format' => 'raw',
                'attribute' => 'dealer_company_id',
                //'filter' => '',
                'value' => function($model) {
                    $dealer = DealerCompany::find()->andWhere(["id"=>$model->dealer_company_id])->one();          
                    $link = Url::to(['dealer-company/view', 'id' => $model->dealer_company_id]);
                    $html = $dealer->getContactSmallLayout($link);
                    return $html;
                },                 
                'headerOptions' => ['width' => '180px'],
            ],            
            [
                'label'=>'Plan Status',
                'format' => 'raw',
                'attribute' => 'plan_status',
                'filter' => InstapPlanPool::allPlanStatus(),
                'value' => function ($model) {
                    return InstapPlanPool::allPlanStatus()[$model->plan_status];
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
                'headerOptions' => ['width' => '100px'],
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template'=>'{view}',
                'buttons' => [
                    'view' => function ($url, $model) {
                        $url = Url::to(['instap-plan-pool/update', 'id'=> $model->id]);
                        return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', $url, ['title' => 'view']); 
                    },
                ],
                'headerOptions' => ['width' => '20px'],
            ],
        ],
    ]); ?>


</div>
<?php

$script = <<< JS

$(document).ready(function () {

    $("#view-more").click(function(){
      $(".role-details").fadeToggle("fast","swing",doSmth);
    });

    function doSmth() {
        // alert(1)
    }

});

JS;
$this->registerJs($script);


?>