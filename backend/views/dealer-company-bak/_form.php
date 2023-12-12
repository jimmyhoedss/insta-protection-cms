<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\DealerCompany;
use common\models\InstapPlan;
use common\models\SysRegion;
use common\models\User;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model common\models\DealerCompany */
/* @var $form yii\widgets\ActiveForm */
$list = DealerCompany::allocationModeArray();
?>

<div class="dealer-form">

    <?php $form = ActiveForm::begin(['enableClientValidation'=>false, 'options' => [ 'id' => 'plan-status-form']]); ?>

    <?php /*$form->field($model, 'region_id')->widget(Select2::classname(), [
            'data' => ArrayHelper::map(SysRegion::find()->all(), 'id', 'name'),
            'options' => ['placeholder' => 'Select Region ...'],
            'pluginOptions' => [
                'allowClear' => true ],
            ])->label('Region');*/
            
    $html = $form->field($model, 'region_id', ['template' => '{input}'])->hiddenInput(['value' => Yii::$app->session->get('region_id')]);

    $plans = InstapPlan::find()->andWhere(["region_id"=>Yii::$app->session->get('region_id')])->active()->all();

    $html .= $form->field($model, 'business_registration_number')->textInput(['maxlength' => true]);
    $html .= $form->field($model, 'business_name')->textInput(['maxlength' => true]);
    $html .=  $form->field($model, 'business_address')->textarea(['rows' => 6]);
    $html .= $form->field($model, 'business_zip_code')->textInput(['maxlength' => true]);
    $html .= $form->field($model, 'business_city')->textInput(['maxlength' => true]);
    $html .= $form->field($model, 'business_state')->textInput(['maxlength' => true]);
    $html .= $form->field($model, 'business_country')->textInput(['maxlength' => true]);
    $html .= $form->field($model, 'business_phone')->textInput(['maxlength' => true]);
    $html .= $form->field($model, 'business_email')->textInput(['maxlength' => true]);
    $html .= $form->field($model, 'business_contact_person')->textInput(['maxlength' => true]);
    $html .= $form->field($model, 'notes')->textarea(['rows' => 6]);

    $html .= $form->field($modelCompanyPlan, 'plan_id_arr')->widget(Select2::classname(), [
            'data' => ArrayHelper::map($plans, 'id', 'name'),
            'options' => ['placeholder' => 'Select Plan ...', 'multiple' => true],
            'pluginOptions' => [
                'allowClear' => true ],
            ])->hint('Please select plan for the company.');

    // $html .= $form->field($modelCompanyPlan, 'plan_id_arr')->checkBoxList(ArrayHelper::map($plans, 'id', 'name'));
    array_pop($list); //remove last item 
    $html .= $form->field($model, 'sp_inventory_order_mode')->radioList(DealerCompany::orderModeArray());
    $html .= "<div class='allocation-mode hidden'>";
    $html .= $form->field($model, 'sp_inventory_allocation_mode')->dropDownList($list)->label("Inventory allocation mode");
    $html .= "</div>";
    $html .= $form->field($model, 'status')->dropDownList([ 'enabled' => 'Enabled', 'disabled' => 'Delete', ], ['prompt' => '']);
    $html .= Html::submitButton(Yii::t('backend', 'Save'), ['class' => 'btn btn-success']);

    echo $html;

    ?>
    <?php ActiveForm::end(); ?>

</div>

<?php

$var1 = DealerCompany::INVENTORY_MODE_STOCKPILE;
$var2 = DealerCompany::INVENTORY_MODE_AD_HOC;

$script = <<< JS

const stockpile = '{$var1}';
const ad_hoc = '{$var2}';

const radioGrpInput = 'input[name="DealerCompany[sp_inventory_order_mode]"]';

$(radioGrpInput).change(function (e) {
    var mode = $(this).val();
    if(mode == stockpile) {
        $(".allocation-mode").removeClass("hidden"); 
    } else {
        $(".allocation-mode").addClass("hidden"); 
    }
});


$(document).ready(function() {    
    if ($(radioGrpInput+":checked").val() == stockpile) {
        $(".allocation-mode").removeClass("hidden"); 
    }
});



JS;
$this->registerJs($script);





/*
var checkRadio = $('#dealercompany-sp_inventory_order_mode').value;
if(checkRadio.value == stockpile) {
    $("section.order-mode").show();
}

$(document).ready(function(){
    $('input[type="radio"]').click(function(){
        var inputValue = $(this).attr("value");
        if(inputValue == stockpile) {
           $("section.order-mode").show();
        }
        if(inputValue == ad_hoc) {
           $("section.order-mode").hide();
        }
    });
});

*/


?>