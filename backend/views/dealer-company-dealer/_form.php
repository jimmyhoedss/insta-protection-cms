<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use kartik\select2\Select2;
use common\models\User;
use common\models\DealerCompany;

/* @var $this yii\web\View */
/* @var $model common\models\DealerCompanyDealer */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="dealer-company-dealer-form">

    <?php 
    
        $c = ArrayHelper::map(DealerCompany::find()->where(['sp_inventory_order_mode' => DealerCompany::INVENTORY_MODE_STOCKPILE, "region_id"=>Yii::$app->session->get('region_id')])->active()->all(), 'id', 'business_name');

        $widget_config = [
                'data' => $c,
                'options' => ['placeholder' => 'Select Company ...'],
                'pluginOptions' => [
                    'allowClear' => true ],
                ];

        $form = ActiveForm::begin(['enableAjaxValidation' => false]); 

        $html = $form->field($model, 'dealer_company_upline_id')->widget(Select2::classname(), $widget_config)->label('Upline');

        $html .= $form->field($model, 'dealer_company_downline_id')->widget(Select2::classname(), $widget_config)->label('Downline');

        echo $html;

    ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('backend', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
