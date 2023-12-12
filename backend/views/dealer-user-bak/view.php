<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\helpers\Url;
use common\models\UserProfile;
use common\models\DealerUser;
use yii\helpers\ArrayHelper;
use common\components\MyCustomActiveRecord;
use common\models\DealerCompany;
use common\models\InstapPlanPool;
use yii\widgets\ActiveForm;
use common\models\DealerUserHistory;
use common\models\User;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $model common\models\DealerUser */

$name = $model->userProfile->first_name ." ". $model->userProfile->last_name;
$this->title = Yii::t('backend', '{name}', [
    'name' => $name,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Staffs'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="dealer-user-view">


    <h4 class='sub-title'><?=Yii::t('backend','Company Details')?></h4>
    <?php echo $model->dealer->getCompanyDetailLayout(); ?>
    <hr>

    <h4 class='sub-title'><?=Yii::t('backend','User Details')?></h4>
    <?php echo $model->userProfile->getUserDetailLayout(); ?>
    <hr>

        <h4 class="sub-title"><?=Yii::t('backend','List of plans sold')?></h4>
        <?= GridView::widget([

        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'summary' => '',

        'columns' => [
            ['class' => 'yii\grid\SerialColumn',
                'headerOptions' => ['width' => '20px']
            ],
            [
                'label'=>Yii::t('backend','Plan Name'),
                'format' => 'raw',
                'attribute' => 'plan_type',
                //'filter' => '',
                'value' => function($model) {       
                    $url = Url::to(['update', 'id' => $model->id]);
                    $html = "<a href='".$url."''>";
                    $html .= $model->planPool->plan->getPlanBanner();
                    $html .= "</a>";
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
                'label'=>Yii::t('backend','Device info'),
                'format' => 'raw',
                'attribute' => 'policy_number',
                  'value' => function($model) {  
                    $html = "";
                    if($model->planPool->plan_status == InstapPlanPool::STATUS_CANCEL || $model->planPool->plan_status == InstapPlanPool::STATUS_REJECT || $model->planPool->plan_status == InstapPlanPool::STATUS_PENDING_REGISTRATION) {
                        // $html .= "<b>Plan status : </b>".$model->planPool->plan_status;
                        
                    } else {
                        $html .= "<b>Brand : </b>".(isset($model->details->sp_brand) ? $model->details->sp_brand : ' — ')."<br>";
                        $html .= "<b>Model : </b>".(isset($model->details->sp_model_number) ? $model->details->sp_model_number : ' — ')."<br>";
                        // $html .= "<b>Plan status : </b>".$model->planPool->plan_status;
                    }
                    return $html;
                },
                'headerOptions' => ['width' => '*'],
            ],
            [
                'format' => 'raw',
                'attribute' => 'policy_number',
                  'value' => function($model) {  
                    $html = $model->planPool->policy_number."<br>";
                    return $html;
                },
                'headerOptions' => ['width' => '*'],
            ],
            [
                'label'=>Yii::t('backend','Plan Status'),
                'format' => 'raw',
                'attribute' => 'policy_number',
                  'value' => function($model) {  
                    $html = $model->planPool->plan_status;
                    return $html;
                },
                'headerOptions' => ['width' => '*'],
            ],
            [   
                'label'=>Yii::t('backend','Coverage Period'),
                'format' => 'raw',
                'attribute' => 'coverage_period',
                //'filter' => '',
                'value' => function($model) { 
                    $html = $model->planPool->getCoverageLayout();
                    // $html .= "[".$model->plan->coverage_period." months]</p>";
                    return $html;
                },                 
                'headerOptions' => ['width' => '150px'],
            ],
            [
                'attribute'=>'notes',
                'format' => 'raw',
                'headerOptions' => ['width' => '250px'],
            ],
            [
                'attribute'=>'created_at',
                'format' => 'raw',
                'value' => function($model) {
                    $d = Yii::$app->formatter->asDatetime($model->created_at);
                    return $d;
                },                 
                'headerOptions' => ['width' => '50px'],
            ],
            [   
                'class' => 'yii\grid\ActionColumn', 
                'template'=>'{view}',
                'buttons' => [
                    'view' => function ($url, $model) {
                        $url = Url::to(['instap-plan-pool/update', 'id'=> $model->planPool->id]);
                        return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', $url, ['title' => 'view']); 
                    },
                ],
                'headerOptions' => ['width' => '20px']
            ],
        ],
    ]); ?>

    <hr>
    <h4 class="sub-title"><?=Yii::t('backend','Staff Movement')?></h4>
       <?php 
            $history = DealerUserHistory::find()->where(["user_id" => $model->user_id])->all();
            $html = "<div class='log-list'>";
            if(!$history){
                echo $html = "<i>* ".Yii::t('backend','No history record')."</i>";
            } else {
                echo DealerUserHistory::getDealerUserHistoryLayout($history);
            }
        ?>
</div>

<?php 

$script = <<< JS
var coll = document.getElementsByClassName("collapsible");
var i;

for (i = 0; i < coll.length; i++) {
  coll[i].addEventListener("click", function() {
    this.classList.toggle("active");
    var content = this.nextElementSibling;
    if (content.style.display === "block") {
      content.style.display = "none";
    } else {
      content.style.display = "block";
    }
  });
}

JS;
$this->registerJs($script);

?>
         