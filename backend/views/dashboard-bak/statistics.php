<?php
/**
 * Eugine Terentev <eugine@terentev.net>
 * @var $this \yii\web\View
 * @var $model \common\models\TimelineEvent
 * @var $dataProvider \yii\data\ActiveDataProvider
 */
use common\components\Utility;
use common\widgets\MyInfoBox;
use yii\helpers\Html;
use yii\helpers\Url;
use common\models\DealerOrder;
use common\models\UserCase;
use common\models\InstapPlan;
use common\models\InstapPlanPool;

$this->title = Yii::t('backend', 'Statistics');
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
<!-- <i>to be redesigned</i> -->
  <!-- Small boxes (Stat box) -->
  <div class="row">
    <?php
      $bgColorCount = 0;
      $plans = InstapPlan::find()->andWhere(["region_id"=>Yii::$app->session->get('region_id')])->active()->asArray()->all();
        $symbol = InstapPlan::currencySymbol()[Yii::$app->session->get('region_id')];

      for ($i=0; $i < count($plans); $i++) { 
        $plan = $plans[$i];
        // print_r($symbol);exit();
        echo '<div class="col-md-6 col-sm-6 col-xs-12">';
        echo '<h2>'.Html::a($plan['name'], Url::to(['instap-plan/view', 'id'=>$plan['id']]));
        echo '<h5>'.Html::a($plan['description']).'</h2><hr>';

        echo MyInfoBox::widget([
          'bgColor'=>$bgColor[$bgColorCount],
          'value'=>InstapPlanPool::find()->where(['plan_id'=>$plan['id']])->andWhere(['plan_status'=>InstapPlanPool::STATUS_PENDING_REGISTRATION])->count(),
          'title' => 'Number of registrations (activations)',
          // 'description' => '<i class="text-muted">'.str_replace("_", " ", InstapPlanPool::STATUS_PENDING_REGISTRATION).'</i>',
          'description' => '# of policies with pending registration status',
          'icon' => '<i class="fa fa-file-signature"></i>',
          // 'link' => Url::to(['instap-plan-pool/index']),
        ]);

        echo MyInfoBox::widget([
          'bgColor'=>$bgColor[$bgColorCount],
          'value'=>InstapPlanPool::find()->where(['plan_id'=>$plan['id']])->andWhere(['plan_status'=>InstapPlanPool::STATUS_REQUIRE_CLARIFICATION])->count(),
          'title' => 'Number of registrations require clarification from customer',
          // 'description' => '<i class="text-muted">'.InstapPlanPool::STATUS_ACTIVE.'</i>',
          'description' => '# of policies with require clarification status',
          'icon' => '<i class="fa fa-question-circle"></i>',
          // 'link' => Url::to(['instap-plan-pool/index']),
        ]);

        echo MyInfoBox::widget([
          'bgColor'=>$bgColor[$bgColorCount],
          'value'=>InstapPlanPool::find()->where(['plan_id'=>$plan['id']])->andWhere(['plan_status'=>InstapPlanPool::STATUS_PENDING_APPROVAL])->count(),
          'title' => 'Number of registrations pending for approval',
          // 'description' => '<i class="text-muted">'.InstapPlanPool::STATUS_ACTIVE.'</i>',
          'description' => '# of policies with pending approval status',
          'icon' => '<i class="fa fa-hourglass-half"></i>',
          // 'link' => Url::to(['instap-plan-pool/index']),
        ]);

        echo MyInfoBox::widget([
          'bgColor'=>$bgColor[$bgColorCount],
          'value'=>InstapPlanPool::find()->where(['plan_id'=>$plan['id']])->andWhere(['plan_status'=>InstapPlanPool::STATUS_ACTIVE])->count(),
          'title' => 'Number of approved registrations',
          // 'description' => '<i class="text-muted">'.InstapPlanPool::STATUS_ACTIVE.'</i>',
          'description' => '# of policies with active status',
          'icon' => '<i class="fa fa-user-shield"></i>',
          // 'link' => Url::to(['instap-plan-pool/index']),
        ]);

        echo MyInfoBox::widget([
          'bgColor'=>$bgColor[$bgColorCount],
          'value'=>InstapPlanPool::find()->where(['plan_id'=>$plan['id']])->andWhere(['plan_status'=>InstapPlanPool::STATUS_PENDING_CLAIM])->count(),
          // 'value'=>UserCase::find()->joinWith('planPool', true)->where(['instap_plan_pool.plan_id'=>$plan['id']])->andWhere(['current_case_status'=>UserCase::CASE_STATUS_CLAIM_PENDING])->count(),
          'title' => 'Number of submitted claims',
          // 'description' => '<i class="text-muted">'.str_replace("_", " ", InstapPlanPool::STATUS_COMPLETE_CLAIM).'</i>',
          'description' => '# of policies with pending claim status',
          'icon' => '<i class="fa fa-notes-medical"></i>',
          // 'link' => Url::to(['instap-plan-pool/index']),
        ]);

        echo MyInfoBox::widget([
          'bgColor'=>$bgColor[$bgColorCount],
          'value'=>InstapPlanPool::find()->where(['plan_id'=>$plan['id']])->andWhere(['plan_status'=>InstapPlanPool::STATUS_COMPLETE_CLAIM])->count(),
          'title' => 'Number of approved claims',
          // 'description' => '<i class="text-muted">'.str_replace("_", " ", InstapPlanPool::STATUS_COMPLETE_CLAIM).'</i>',
          'description' => '# of policies with completed claim status',
          'icon' => '<i class="fa fa-clipboard-check"></i>',
          // 'link' => Url::to(['instap-plan-pool/index']),
        ]);

        $plan_sold = (float)InstapPlanPool::find()->where(['plan_id'=>$plan['id']])->andWhere(['not', ['plan_status'=> InstapPlanPool::STATUS_CANCEL]])->count();

        echo MyInfoBox::widget([
          'bgColor'=>$bgColor[$bgColorCount],
          'value'=>$plan_sold,
          'title' => 'Number of policies sold',
          // 'description' => '<i class="text-muted">'.str_replace("_", " ", InstapPlanPool::STATUS_COMPLETE_CLAIM).'</i>',
          'description' => '# of policies sold (statuses that are not CANCEL)',
          'icon' => '<i class="fa fa-hand-holding-usd"></i>',
          // 'link' => Url::to(['instap-plan-pool/index']),
        ]);



        echo MyInfoBox::widget([
          'bgColor'=>$bgColor[$bgColorCount],
          'value'=>$symbol.' '.number_format(((float)InstapPlanPool::find()->where(['plan_id'=>$plan['id']])->andWhere(['not', ['plan_status'=> InstapPlanPool::STATUS_CANCEL]])->andWhere(['not', ['plan_status'=> InstapPlanPool::STATUS_REJECT]])->count() * $plan['premium_price']), 2, '.', ''),
          'title' => 'Total premium payable',
          'description' => '# of policies activated (statuses that are not CANCEL or REJECT) * plan\'s premium price ('.$symbol.' '.$plan['premium_price'].')',
          'icon' => '<i class="fa fa-file-invoice-dollar"></i>',
          // 'link' => Url::to(['instap-plan-pool/index']),
        ]);

        echo MyInfoBox::widget([
          'bgColor'=>$bgColor[$bgColorCount],
          'value'=>$symbol.' '.number_format(($plan_sold * $plan['dealer_price']), 2, '.', ''),
          'title' => 'Total sales by IP',
          'description' => '# of policies sold (statuses that are not CANCEL) * plan\'s dealer price ('.$symbol.' '.$plan['dealer_price'].')',
          'icon' => '<i class="fa fa-comment-dollar"></i>',
          // 'link' => Url::to(['instap-plan-pool/index']),
        ]);

        echo MyInfoBox::widget([
          'bgColor'=>$bgColor[$bgColorCount],
          'value'=>$symbol.' '.number_format(($plan_sold * $plan['retail_price']), 2, '.', ''),
          'title' => 'Total sales by Partners',
          'description' => '# of policies sold (statuses that are not CANCEL) * plan\'s retail price ('.$symbol.' '.$plan['retail_price'].')',
          'icon' => '<i class="fa fa-receipt"></i>',
          // 'link' => Url::to(['instap-plan-pool/index']),
        ]);

        echo MyInfoBox::widget([
          'bgColor'=>$bgColor[$bgColorCount],
          'value'=>$symbol.' '.number_format(($plan_sold *  $plan['dealer_price']) - ($plan_sold * $plan['premium_price']), 2, '.', ''),
          'title' => 'Total revenue by IP',
          'description' => '(# of policies sold (statuses that are not CANCEL) * plan\'s dealer price ('.$symbol.' '.$plan['dealer_price'].')) - (# of policies sold (statuses that are not CANCEL) * plan\'s premium price ($'.$plan['premium_price'].'))',
          'icon' => '<i class="fa fa-funnel-dollar"></i>',
          // 'link' => Url::to(['instap-plan-pool/index']),
        ]);

        echo MyInfoBox::widget([
          'bgColor'=>$bgColor[$bgColorCount],
          'value'=>$symbol.' '.number_format(($plan_sold * $plan['retail_price']) - ($plan_sold * $plan['dealer_price']), 2, '.', ''),
          'title' => 'Total revenue by Partners',
          'description' => '(# of policies sold (statuses that are not CANCEL) * plan\'s retail price ('.$symbol.' '.$plan['retail_price'].')) - (# of policies sold (statuses that are not CANCEL) * plan\'s dealer price ($'.$plan['dealer_price'].'))',
          'icon' => '<i class="fa fa-dollar-sign"></i>',
          // 'icon' => '<i class="fa fa-dollar-sign">&nbsp;</i><span class="label label-warning" style="font-size: 16px;"><i class="fa fa-plus"></i></span>',
          // 'link' => Url::to(['instap-plan-pool/index']),
        ]);

        echo MyInfoBox::widget([
          'bgColor'=>$bgColor[$bgColorCount],
          'value'=>$plan_sold>0?number_format((float)(InstapPlanPool::find()->where(['plan_id'=>$plan['id']])->andWhere(['plan_status'=>InstapPlanPool::STATUS_COMPLETE_CLAIM])->count()/$plan_sold), 2, '.', ''):0,
          'title' => 'Claim Ratio',
          'description' => '# of policies claimed / # of policies sold (statuses that are not CANCEL)',
          'icon' => '<i class="fa fa-exchange-alt"></i>',
          // 'link' => Url::to(['instap-plan-pool/index']),
        ]);
        echo '</div>';
        $bgColorCount = $bgColorCount >= (count($bgColor) - 1) ? 0 : $bgColorCount+1;
      }
    ?>
  </div>
