<?php

use yii\helpers\Html;
use common\models\DealerCompany;
use common\models\DealerInventoryAllocationHistory;
/* @var $this yii\web\View */
/* @var $model common\models\DealerOrderInventoryOverview */

$this->title = Yii::t('backend', $model->dealer->business_name, [
    'name' => $model->id,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Inventory Overviews'), 'url' => ['index']];
$this->params['breadcrumbs'][] = Yii::t('backend', 'Inventory History');
?>
<div class="dealer-order-inventory-overview-update">

   <!--  <?= $this->render('_form', [
        'model' => $model,
    ]) ?> 
 -->
    <!-- <hr> -->
    <h4 class="sub-title"><?=Yii::t('backend',"Inventory History")?></h4> 

    <?php

        $html = "<div class='log-list'>";
        $models =$dataProvider->models;

        echo \yii\widgets\LinkPager::widget([
            'pagination'=>$dataProvider->pagination,
        ]);
        foreach($models as $m) {
            $from_company = "<b> ".DealerCompany::find()->andWhere(["id"=>$m->from_company_id])->one()->business_name ."</b>";
            $to_company = "<b> ".DealerCompany::find()->andWhere(["id"=>$m->to_company_id])->one()->business_name."</b>";
            $plan = "<b> ".$m->plan->name."</b>";
            $date = Yii::$app->formatter->asDatetime($m->created_at);
            $action = "<b>".$m->action."</b>";
            $amount = "<b>".$m->amount."</b>";
            if($m->action == DealerInventoryAllocationHistory::ACTION_ALLOCATE){
                $html .= "<i>" . $date . "&nbsp; :  " .$from_company . "&nbsp;". $action ."&nbsp;".$amount."&nbsp;of".$plan." to". $to_company."<br>"; 
            }
            if($m->action == DealerInventoryAllocationHistory::ACTION_ACTIVATE){
                 $html .= "<i>" . $date . "&nbsp; :  " .$from_company . "&nbsp;". $action ."&nbsp;".$amount."&nbsp;of".$plan."<br>"; 
            }
        }
        $html .= "</div>";
        echo \yii\helpers\HtmlPurifier::process($html);

        echo \yii\widgets\LinkPager::widget([
            'pagination'=>$dataProvider->pagination,
        ]);

    ?>

</div>
