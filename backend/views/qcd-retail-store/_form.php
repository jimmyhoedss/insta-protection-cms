<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use common\models\DealerCompany;
use common\models\QcdRetailStore;
use common\models\QcdDeviceMaker;
use common\models\InstapPlan;
use common\models\SysRegion;

?>

<div class="qcd-repair-centre-form">

   <?php $form = ActiveForm::begin();

        $html = "";
        // $html = $form->field($model, 'id')->widget(Select2::classname(), [
        //         'data' => ArrayHelper::map(QcdRepairCentre::find()->all(), 'id', 'repair_centre'),
        //         'options' => ['placeholder' => 'Select Repair Centre ...'],
        //         'pluginOptions' => [
        //             'allowClear' => true ],
        //         ])->label('Repair Centre');
        $html .= $form->field($model, 'retail_store')->textInput(['maxlength' => true]);
        $html .= $form->field($model, 'country_code', ['template' => '{input}'])->hiddenInput(['value' => Yii::$app->session->get('region_id')]);
        // $html .= $form->field($model, 'country_code')->widget(Select2::classname(), [
        //         'data' => ArrayHelper::map(SysRegion::find()->all(), 'id', 'name'),
        //         'options' => ['placeholder' => 'Select Region ...'],
        //         'pluginOptions' => [
        //             'allowClear' => true ],
        //         ])->label('Region');

        $html .= $form->field($model, 'state_code')->textInput(['maxlength' => true]);
        $html .= $form->field($model, 'state_name')->textInput(['maxlength' => true]);
        $html .= $form->field($model, 'city_name')->textInput(['maxlength' => true]);
        $html .= $form->field($model, 'opening_hours')->textInput(['maxlength' => true]);
        $html .= $form->field($model, 'email')->textInput(['maxlength' => true]);
        $html .= $form->field($model, 'telephone')->textInput(['maxlength' => true]);
        $html .= $form->field($model, 'state')->textInput(['maxlength' => true]);
        $html .= $form->field($model, 'address')->textarea(['rows' => 6]); 


        $html .= $form->field($retailStoreForm, 'brand_id_arr')->widget(Select2::classname(), [
                'data' => ArrayHelper::map(QcdDeviceMaker::find()->all(), 'device_maker_id', 'device_maker'),
                'options' => ['placeholder' => 'Select Brand ...', 'multiple' => true],
                'pluginOptions' => [
                    'allowClear' => true ],
                ])->hint('Brand allow to repair in repair centre');

        $html .= $form->field($retailStoreForm, 'plan_id_arr')->widget(Select2::classname(), [
                'data' => ArrayHelper::map(InstapPlan::find()->all(), 'id', 'name'),
                'options' => ['placeholder' => 'Select Brand ...', 'multiple' => true],
                'pluginOptions' => [
                    'allowClear' => true ],
                ])->hint('Plan allow to use in retail centre');

        // $html .= $form->field($model, 'status')->dropDownList([ 'enabled' => 'Enabled', 'disabled' => 'Disabled', ], ['prompt' => '']); 

        echo $html;
    ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('backend', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
