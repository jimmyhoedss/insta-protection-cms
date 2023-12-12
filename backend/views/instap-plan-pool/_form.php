<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\InstapPlanPool */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="instap-plan-pool-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'plan_id')->textInput() ?>

    <?= $form->field($model, 'dealer_company_id')->textInput() ?>

    <?= $form->field($model, 'user_id')->textInput() ?>

    <?= $form->field($model, 'region_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'plan_category')->dropDownList([ 'AP' => 'AP', 'MO' => 'MO', 'SP' => 'SP', 'TR' => 'TR', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'plan_sku')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'policy_number')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'plan_status')->dropDownList([ 'pending_registration' => 'Pending registration', 'pending_physical_assessment' => 'Pending physical assessment', 'pending_approval' => 'Pending approval', 'require_clarification' => 'Require clarification', 'active' => 'Active', 'expired' => 'Expired', 'rejected' => 'Rejected', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'notes')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'coverage_start_at')->textInput() ?>

    <?= $form->field($model, 'coverage_end_at')->textInput() ?>

    <?= $form->field($model, 'status')->dropDownList([ 'enabled' => 'Enabled', 'disabled' => 'Disabled', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'created_by')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <?= $form->field($model, 'updated_by')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('backend', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
