<?php
/**
 * Eugine Terentev <eugine@terentev.net>
 * @var $this \yii\web\View
 * @var $model \common\models\TimelineEvent
 * @var $dataProvider \yii\data\ActiveDataProvider
 */
use common\components\Utility;
use backend\assets\ChartAsset;
use common\widgets\MyInfoBox;
use yii\helpers\Html;
use yii\helpers\Url;
use common\models\User;
use common\models\DealerOrder;
use common\models\UserCase;
use common\models\InstapPlan;
use common\models\InstapPlanPool;
use common\models\UserPlanDetailEdit;

ChartAsset::register($this);

$this->title = Yii::t('backend', 'Dashboard');


$icons = [
    'user'=>'<i class="fa fa-user bg-blue"></i>'
];
$bgColor = [
  'bg-aqua',
  'bg-blue',
  'bg-navy',
  'bg-green',
  'bg-olive',
  'bg-lime',
  'bg-yellow',
  'bg-orange',
  'bg-red',
  'bg-fuchsia',
  'bg-purple',
  'bg-maroon',
]
?>

    
<div class="dashboard-index">


    
  <!-- Small boxes (Stat box) -->
  <!-- <div class="row"> -->
      <!-- <button id="delete">Delete</button> -->
      <?php
      /*  if (Yii::$app->authManager->checkAccess(Yii::$app->user->id, User::ROLE_ADMINISTRATOR)) {
          $plans = InstapPlan::find()->select(['id'])->andWhere(["region_id"=>Yii::$app->session->get('region_id')])->active()->indexBy('id')->asArray()->all();
          if (!Yii::$app->user->can(User::ROLE_IP_MANAGER)) {
            echo '<div class="col-md-3 col-sm-6 col-xs-12">';
              echo MyInfoBox::widget([
                'bgColor'=>$bgColor[0],
                'value'=>UserPlanDetailEdit::countTotalPendingEditApproval(),
                'title' => 'Policies\' detail pending approval',
                // 'description' => '# of polices with pending approval status',
                'icon' => '<i class="fa fa-file-signature"></i>',
                'link' => Url::to(['instap-plan-pool/pending-approval']),
              ]);
            echo '</div>';
          }
          if (!Yii::$app->user->can(User::ROLE_IP_ADMIN_ASSISTANT)) {
            echo '<div class="col-md-3 col-sm-6 col-xs-12">';
              echo MyInfoBox::widget([
                'bgColor'=>$bgColor[1],
                'value'=>InstapPlanPool::countTotalPendingApproval(),
                'title' => 'Policies pending approval',
                // 'description' => '# of polices with pending approval status',
                'icon' => '<i class="fa fa-file-signature"></i>',
                'link' => Url::to(['instap-plan-pool/pending-approval']),
              ]);
            echo '</div>';
            echo '<div class="col-md-3 col-sm-6 col-xs-12">';
              echo MyInfoBox::widget([
                'bgColor'=>$bgColor[2],
                'value'=>UserCase::find()->joinWith('planPool', true)->where(['instap_plan_pool.plan_id'=>array_keys($plans)])->andWhere(['current_case_status'=>UserCase::CASE_STATUS_CLAIM_PENDING])->count(),
                'title' => 'Claims pending approval',
                // 'description' => '# of claims with pending approval status',
                'icon' => '<i class="fa fa-notes-medical"></i>',
                'link' => Url::to(['user-case/claim-pending']),
              ]);
            echo '</div>';
          }
        }*/
      ?>
    
  <!-- </div> -->
  <!-- Small boxes (Stat box) -->

  <!-- resize chart ref: https://www.chartjs.org/docs/latest/general/responsive.html#configuration-options -->
  <div class="chart-container" style="position: relative; height:50vh; width:80vw; margin: 0 auto; background-color: white; padding: 5px;">
    <canvas id="myChart"></canvas>
  </div> 

</div>



