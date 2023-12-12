<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use backend\assets\ChartAsset;
use yii\web\View;
use common\assets\FontAwesome;
use backend\assets\BackendAsset;
use common\models\DealerCompany;
use common\models\DealerCompanyDealer;




/* @var $this yii\web\View */
/* @var $model common\models\DealerCompanyDealer */

$this->title = Yii::t('backend',"Company Organisation");
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Companies'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->business_name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = $this->title;
ChartAsset::register($this);

?>

<div class="dealer-company-org_chart">
    <div class="chart" id="org-chart"></div>

</div>



<?php 

    $param = "";
    $js = "";
    if($topmost_id) {
        $dc_top = DealerCompany::find()->where(['id' => $topmost_id])->one();
        $id = $topmost_id;
        $var = "co".$id;
        $js.= "var ".$var. " = { ";
        $js.= "text: {";
        $js.=  "name:'".str_replace("'","’",$dc_top->business_name)."'";
        $js.= "},"; 
        if($model->id == $topmost_id) {
            $js.= "HTMLclass:'highlight_red'";
        } 
        $js.= "}; ";
        $param.=$var . ", ";
    }

    for($i=0;$i<count($comp_arr);$i++) {
        $c = $comp_arr[$i];
        $id = $c["dealer_company_downline_id"];
        $var = "co".$id;
        $js.= "var ".$var. " = { ";

        if (isset($c["dealer_company_upline_id"])) {
            $js.="parent: co".$c["dealer_company_upline_id"].", ";
        }
        $dc = DealerCompany::find()->where(['id' => $c["dealer_company_downline_id"]])->one();
        $js.= "text: {";
        $js.=  "name:'" .str_replace("'","’",$dc->business_name). "'";
        $js.= "},"; 
        if($model->id == $c["dealer_company_downline_id"]) {
            $js.= "HTMLclass:'highlight_red'";
        } 
        $js.= "}; ";
        $param.=$var . ", ";

    }
    // print_r($js);
    // exit();




$script = <<<JS
    var config = {  
        container: "#org-chart",
        
        connectors: {
            type: 'step'
        },
        node: {
            HTMLclass: 'nodeExample1'
        }
    };
   
   {$js}

    var chart_config = [
        config,
        {$param}

    ];
    console.log(chart_config)

    new Treant( chart_config );

JS;

    $this->registerJs($script, View::POS_END);

?>
