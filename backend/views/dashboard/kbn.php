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

use backend\widgets\TabMenuDashboardWidget;

use yii\bootstrap\Dropdown;

ChartAsset::register($this);

$this->title = Yii::t('backend', 'Dashboard');

// $plans = InstapPlan::find()->select(['id', 'name'])->where(['region_id' => Yii::$app->session->get('region_id')])->asArray()->all();


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

// $all_plans = InstapPlan::find()->select(['id', 'name'])->where(['region_id' => Yii::$app->session->get('region_id')])->asArray()->all();




// $session->set('date_time_category', 'month');

// print_r($session->get('date_time_category')); exit();


?>
<style type="text/css">



</style>
<script>
  function resizeIframe(obj) {
    obj.style.height = obj.contentWindow.document.documentElement.scrollHeight + 'px';
  }
</script>
    
<div class="dashboard-index">

 <?php 
        echo TabMenuDashboardWidget::widget(['page'=> Yii::$app->session->get('date_time_category')]);
    ?>



    <!-- <iframe src="http://localhost:5601/app/dashboards#/view/f2643520-a0cf-11eb-baa4-b7b553fd251d?embed=true&_g=(filters%3A!()%2CrefreshInterval%3A(pause%3A!t%2Cvalue%3A0)%2Ctime%3A(from%3Anow-90d%2Cto%3Anow))&hide-filter-bar=true" height="600" width="800"></iframe>

    
 -->

    <iframe src="http://localhost:5601/app/dashboards#/view/f2643520-a0cf-11eb-baa4-b7b553fd251d?embed=true&_g=(filters%3A!()%2CrefreshInterval%3A(pause%3A!t%2Cvalue%3A0)%2Ctime%3A(from%3Anow-95d%2Cto%3Anow))&show-time-filter=true&hide-filter-bar=true" height="600" width="800" onload="resizeIframe(this)"></iframe>

    <iframe src="http://localhost:5601/goto/d5dac33f6e7366dece7769cc3b882563" height="600" width="800"></iframe>

  <!-- resize chart ref: https://www.chartjs.org/docs/latest/general/responsive.html#configuration-options -->

  
 

</div>



