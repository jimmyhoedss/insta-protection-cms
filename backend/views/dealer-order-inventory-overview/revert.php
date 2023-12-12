<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use kartik\select2\Select2;
use common\models\User;
use common\models\DealerCompany;
use common\models\InstapPlan;
use common\models\DealerOrderInventoryOverview;

/* @var $this yii\web\View */
/* @var $model common\models\DealerOrderInventoryOverview */

$this->title = Yii::t('backend', 'Revert Stocks');
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Inventories'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="dealer-order-inventory-overview-revert">

<?php 

        $c = ArrayHelper::map(DealerCompany::find()->where(['sp_inventory_order_mode' => DealerCompany::INVENTORY_MODE_STOCKPILE])->andWhere(["region_id"=>Yii::$app->session->get('region_id')])->active()->all(), 'id', 'business_name');
        $p = ArrayHelper::map(InstapPlan::find()->active()->andWhere(["region_id"=>Yii::$app->session->get('region_id')])->all(), 'id', 'name');
        $widget_config1 = [
                    'data' => $c,
                    'options' => ['placeholder' => 'Select Company ...'],
                    'pluginOptions' => [
                        'allowClear' => true ],
                ];

        $widget_config2 = [
                    'data' => $p,
                    'options' => ['placeholder' => 'Select Plan ...'],
                    'pluginOptions' => [
                        'allowClear' => true ],
                ];

        //form
        $form = ActiveForm::begin(); 
        $html = $form->field($model, 'mode_stock_revert')->dropDownList(DealerOrderInventoryOverview::allRevertMode())->label("Inventory revert mode");
        $html .=   $form->field($model, 'dealer_company_id')->widget(Select2::classname(), $widget_config1)->label('Company');      
        $html .=  $form->field($model, 'plan_id')->widget(Select2::classname(), $widget_config2)->label('Plan');        
        $html .=  $form->field($model, 'assign_amount')->textInput()->label('Amount');

        echo $html;

    ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('backend', 'Revert'), ['class' => 'btn btn-danger', 'data' => [
                'confirm' => Yii::t('backend', 'Are you sure you want to revert?'),
                'method' => 'post',
            ],]) ?>
    </div>

    <?php ActiveForm::end(); ?>

    <p class="text-muted h4">
        <i>[REVERT IP ALLOCATE] - Revert stock for a company doesn't have any upline. (eg. master distrubuter)</i><br>
        <i>[REVERT DEALER ALLOCATE] - Revert stock back to its upline company.</i><br>
        <i>[REVERT DEALER ACTIVATE] - Revert stock back to the company itself.</i>
    </p>

</div>
