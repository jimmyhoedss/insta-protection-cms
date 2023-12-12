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
use common\models\DealerOrderInventoryOverview;
use common\models\form\DashboardForm;
use kartik\form\ActiveForm;
use backend\widgets\TabMenuDashboardWidget;
use kartik\daterange\DateRangePicker;
use yii\bootstrap\Dropdown;

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
];

//url to plan pending approval
$url1 = Url::to(['instap-plan-pool/pending-approval']);
// $url1 = Url::to(['instap-plan-pool/index', 'InstapPlanPoolSearch[plan_status]'=>InstapPlanPool::STATUS_ACTIVE]);
$url2 = Url::to(['user-case/claim-pending']);




?>
<style type="text/css">

</style>
    
<div class="dashboard-index">
<div class="col-12 col-md-9">
 <?php 
        // echo TabMenuDashboardWidget::widget(['page'=> Yii::$app->session->get('date_time_category')]);
    ?>
</div>

<div class="col-12 col-md-8">

    <?php 

      $form = ActiveForm::begin([]);

      echo $form->errorSummary($model);

      echo $form->field($model, 'dateRange', [
          'addon'=>['prepend'=>['content'=>'<i class="glyphicon glyphicon-calendar"></i>']],
          'options'=>['class'=>'drp-container form-group'],
      ])->widget(DateRangePicker::classname(), [
          'useWithAddon'=>true,
          'convertFormat'=>true,
          'options' => ['autocomplete' => 'off'],
          'startAttribute' => 'date_start',
          'endAttribute' => 'date_end',
          'startInputOptions'=> ['value' => Yii::$app->formatter->asDate($model->date_start)],
          'endInputOptions'=> ['value' => Yii::$app->formatter->asDate($model->date_end)],
          'pluginOptions'=>[
          'locale'=>['format' => 'd M Y'],
          ]
        ]);
      

    ?>
      <div class="form-group">
          <?php echo Html::submitButton(Yii::t('backend', 'Filter'), ['class' => 'btn btn-success']) ?>
      </div>

      <?php ActiveForm::end(); ?>

</div>
    
  

  <!-- resize chart ref: https://www.chartjs.org/docs/latest/general/responsive.html#configuration-options -->
<!-- <div class="row">
  <div class="col-sm-8">col-sm-8</div>
  <div class="col-sm-4">col-sm-4</div>
</div> -->
<!-- style="display: flex; flex-direction: row;" -->
  
  <div class="row">
    <div class="col-12 col-md-8">
      <div class="chart-container">
        <img class="loading" src="https://i.imgur.com/fXUIBfi.gif" alt="Chart will Render Here..."/>
        <canvas id="plan-registration"></canvas>
      </div> 
    </div>
    <div class="col-6  col-md-4">
       <div class="sub-chart-container" style="height: 350px;">
        <canvas id="sub-plan-registration" width="800" height="680"></canvas>
      </div>
    </div>

  </div>
<br>
  <div class="row">
    <div class="col-12 col-md-8">
      <div class="chart-container">
        <img class="loading" src="https://i.imgur.com/fXUIBfi.gif" alt="Chart will Render Here..."/>
        <canvas id="claims"></canvas>
      </div>  
    </div>
    <div class="col-6 col-md-4">
      <div class="sub-chart-container" style="height: 350px;">
        <canvas id="sub-claims" width="800" height="680"></canvas>
      </div>
      
    </div>
  </div>
  
  <hr>
  <div class="row">
    <!--  -->
    <div class="col-6  col-md-12">
      <div class="custom-dropdown">
          <!-- <label for="plans" >Select A Plan:</label> -->
          <select name="plans" id="plans" class="dropdown-text">
            <?php 
              foreach ($all_plans as $plan) {
                echo '<option value="'.$plan['id'].'">'.$plan['name'].'</option>';
              }

            ?>
          </select>
      </div>
    </div>
    <div class="col-6  col-md-4">
    <!-- dropdownlist -->
      
    <!-- dropdownlist -->
      <div class="small-container">
        <h1><span class="small-text">Registration</span><span class="number" id="total_reg_plan">0</span></h1>
        <div class="inner-container">
          <p>Approved: <span id="approved">0</span></p>
          <p><a href="<?=$url1?>">Pending approval: <span id="pending_approvval">0</span></a></p>
          <p>Pending clarification: <span id="clarification">0</span></p>
          <p>Cancelled: <span id="cancelled">0</span></p>
        </div>
      </div>
    </div>
    <!--  -->

<!--  -->
    <div class="col-6  col-md-4">
       <div class="small-container">
          <h1><span class="small-text">Claim</span><span  class="number" id="total_reg_claim">0</span></h1>
          <div class="inner-container">
            <p>Approved: <span id="claim_approved">0</span></p>
            <p><a href="<?=$url2?>">Pending approval: <span id="claim_pending_approvval">0</span></a></p>
            <p>Pending clarification: <span id="claim_clarification">0</span></p>
            <p>Claim Ratio: <span id="claim_ratio">0</span></p>
          </div>
        </div>
      </div>
<!--  -->

    <div class="col-6 col-md-4">
        <div class="small-container">
          <h1><span class="small-text">Revenue</span><span class="number" id="revenue">0</span></h1>
          <div class="inner-container">
            <p>Premium payable: <span id="total_premium">0</span></p>
            <p>Sales by distributor: <span id="total_dealer">0</span></p>
            <p>Sales by retailor: <span id="total_retail">0</span></p>
            <br>
            <p></p>
          </div>
        </div>
    </div>  

  </div>



  <br>

  <!-- Small boxes (Stat box) -->
  <div class="row">
      <!-- <button id="delete">Delete</button> -->
      <?php
        // if (Yii::$app->authManager->checkAccess(Yii::$app->user->id, User::ROLE_ADMINISTRATOR)) {
          $plans = InstapPlan::find()->select(['id'])->andWhere(["region_id"=>Yii::$app->session->get('region_id')])->active()->indexBy('id')->asArray()->all();
          if (!Yii::$app->user->can(User::ROLE_IP_MANAGER)) {
            echo '<div class="col-md-3 col-sm-6 col-xs-12">';
              echo MyInfoBox::widget([
                'bgColor'=>$bgColor[0],
                'value'=>UserPlanDetailEdit::countTotalPendingEditApproval(),
                'title' => 'Policy Edit Request',
                // 'description' => '# of polices with pending approval status',
                'icon' => '<i class="fa fa-file-signature"></i>',
                'link' => Url::to(['instap-plan-pool/pending-edit-approval']),
              ]);
            echo '</div>';
          }
          if (!Yii::$app->user->can(User::ROLE_IP_ADMIN_ASSISTANT)) {
            echo '<div class="col-md-3 col-sm-6 col-xs-12">';
              echo MyInfoBox::widget([
                'bgColor'=>$bgColor[1],
                'value'=>InstapPlanPool::countTotalPendingApproval(),
                'title' => 'Policy Approval Request',
                // 'description' => '# of polices with pending approval status',
                'icon' => '<i class="fa fa-user-shield"></i>',
                'link' => $url1,
              ]);
            echo '</div>';
            
            echo '<div class="col-md-3 col-sm-6 col-xs-12">';
              echo MyInfoBox::widget([
                'bgColor'=>$bgColor[1],
                'value'=>UserCase::find()->joinWith('planPool', true)->where(['instap_plan_pool.plan_id'=>array_keys($plans)])->andWhere(['current_case_status'=>UserCase::CASE_STATUS_CLAIM_PENDING])->count(),
                'title' => 'Claim Approval Request',
                // 'description' => '# of polices with pending approval status',
                'icon' => '<i class="fa fa-file-signature"></i>',
                'link' => Url::to(['user-case/claim-pending']),
              ]);
            echo '</div>';

            echo '<div class="col-md-3 col-sm-6 col-xs-12">';
              echo MyInfoBox::widget([
                'bgColor'=>$bgColor[2],
                'value'=>$query = User::find()->innerJoinWith('userProfile', true)->andWhere(["region_id"=>Yii::$app->session->get('region_id')])->andWhere(["MONTH(STR_TO_DATE(birthday, '%d-%m-%Y'))" => date('m')])->asArray()->count(),
                'title' => 'Birthday Alert',
                // 'description' => '# of claims with pending approval status',
                'icon' => '<i class="fa fa-birthday-cake"></i>',
                'link' => Url::to(['user/birthday-month']),
              ]);
            echo '</div>';


            echo '<div class="col-md-3 col-sm-6 col-xs-12">';
              echo MyInfoBox::widget([
                'bgColor'=>$bgColor[1],
                'value'=> count(DealerOrderInventoryOverview::getCompanyWithLowInventory(20)),
                'title' => 'Low Inventory Alert',
                // 'description' => '# of polices with pending approval status',
                'icon' => '<i class="fa fa-warehouse"></i>',
                'link' => Url::to(['dealer-order-inventory-overview/low-inventory', 'amount' => 20]),
              ]);
            echo '</div>';
          }
        // }
      ?>
    
  </div>
  <!-- Small boxes (Stat box) -->

  <hr>

  <div class="row">
    <div class="col-12 col-md-12">
      <div class="chart-container">
        <!-- <img class="loading" src="https://i.imgur.com/fXUIBfi.gif" alt="Chart will Render Here..."/> -->
        <canvas id="revenue-chart"></canvas>
      </div> 
    </div>
  </div>

  <hr>

  <ul class="nav nav-tabs">
    <!-- <li class="active"><a data-toggle="tab" href="#month">RANKING OF THE MONTH (Last 3 months)</a></li> -->
    <li class="dropdown">
      <a class="dropdown-toggle" data-toggle="dropdown" href="#">RANKING OF THE MONTH <b class="caret"></b></a>
      <ul class="dropdown-menu">
          <li><a href="#this-month" role="tab" data-toggle="tab">This Month</a></li>
          <li><a href="#last-month" role="tab" data-toggle="tab">Last Month</a></li>
          <li><a href="#the-month-before-last" role="tab" data-toggle="tab">The Month Before Last</a></li>
      </ul>
    </li>
    <li><a data-toggle="tab" href="#year">RANKING OF THE YEAR (Last year)</a></li>
  </ul>


  <div class="tab-content">
  
    <div id="year" class="tab-pane fade">
      <!-- <h3>RANKING OF THIS YEAR</h3> -->
      <br>
        <div class="row">
          <div class="col-6 col-md-6 col-sm-12">
              <div class="box box-primary"> 
                <div class="box-body">
                <?=DashboardForm::mostSoldPlanByCompany($year_start,$year_end)?>
                </div>
            </div>
          </div>

          <div class="col-6 col-md-6 col-sm-12">
              <div class="box box-primary"> 
                <div class="box-body">
                  <?=DashboardForm::mostSoldPlanByStaff($year_start,$year_end)?>
                </div>
              </div>
          </div>
        </div>

    </div>

    <!--  -->
    <div id="this-month" class="tab-pane fade in active">
      <br>
        <div class="row">
          <div class="col-6 col-md-6 col-sm-12">
              <div class="box box-primary"> 
                <div class="box-body">
                <?=DashboardForm::mostSoldPlanByCompany($this_month_start,$this_month_end)?>
                </div>
            </div>
          </div>

          <div class="col-6 col-md-6 col-sm-12">
              <div class="box box-primary"> 
                <div class="box-body">
                  <?=DashboardForm::mostSoldPlanByStaff($this_month_start,$this_month_end)?>
                </div>
              </div>
          </div>
        </div>

    </div>

    <div id="last-month" class="tab-pane fade">
        <br>      
        <div class="row">
          <div class="col-6 col-md-6 col-sm-12">
              <div class="box box-primary"> 
                <div class="box-body">
                <?=DashboardForm::mostSoldPlanByCompany($last_month_start,$last_month_end)?>
                </div>
            </div>
          </div>

          <div class="col-6 col-md-6 col-sm-12">
              <div class="box box-primary"> 
                <div class="box-body">
                  <?=DashboardForm::mostSoldPlanByStaff($last_month_start,$last_month_end)?>
                </div>
              </div>
          </div>
        </div>
    </div>

    <div id="the-month-before-last" class="tab-pane fade">
        <br>
        <div class="row">
          <div class="col-6 col-md-6 col-sm-12">
              <div class="box box-primary"> 
                <div class="box-body">
                <?=DashboardForm::mostSoldPlanByCompany($last_2_month_start,$last_2_month_end)?>
                </div>
            </div>
          </div>

          <div class="col-6 col-md-6 col-sm-12">
              <div class="box box-primary"> 
                <div class="box-body">
                  <?=DashboardForm::mostSoldPlanByStaff($last_2_month_start,$last_2_month_end)?>
                </div>
              </div>
          </div>
        </div>
    </div>

  </div>


</div>



