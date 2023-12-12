<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\UserPlanDetail */
/* @var $form yii\widgets\ActiveForm */

//$form->field($model, 'plan_pool_id')->textInput()
?>

<div class="user-plan-detail-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->errorSummary($model) ?>

    <?= $form->field($model, 'sp_brand')->textInput(['maxlength' => true, 'disabled' => true]) ?>

    <?= $form->field($model, 'sp_model_number')->textInput(['maxlength' => true, 'disabled' => true]) ?>

    <?= $form->field($model, 'sp_model_name')->textInput(['maxlength' => true, 'disabled' => true]) ?>

    <?= $form->field($model, 'sp_serial')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'sp_imei')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'sp_color')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'notes')->textarea(['rows' => 5]) ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('backend', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
