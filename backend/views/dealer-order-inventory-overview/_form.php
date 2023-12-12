<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use kartik\select2\Select2;
use common\models\User;
use common\models\DealerCompany;
use common\models\InstapPlan;

/* @var $this yii\web\View */
/* @var $model common\models\DealerOrderInventoryOverview */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="dealer-order-inventory-overview-form">

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

        $html =   $form->field($model, 'dealer_company_id')->widget(Select2::classname(), $widget_config1)->label('Company');      
        $html .=  $form->field($model, 'plan_id')->widget(Select2::classname(), $widget_config2)->label('Plan');        
        $html .=  $form->field($model, 'assign_amount')->textInput()->label('Amount');

        echo $html;

    ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('backend', 'Allocate'), ['class' => 'btn btn-success', 'data' => [
                'confirm' => Yii::t('backend', 'Are you sure you want to allocate?'),
                'method' => 'post',
            ],]) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
