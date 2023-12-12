<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\User */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="user-form">

    <?php $form = ActiveForm::begin(); ?>

    <?php echo $form->errorSummary($model); ?>

    <?php echo $form->field($model, 'region_id')->textInput() ?>

    <?php echo $form->field($model, 'mobile_calling_code')->textInput() ?>

    <?php echo $form->field($model, 'mobile_number')->textInput() ?>

    <?php echo $form->field($model, 'password_salt')->textInput(['maxlength' => true]) ?>

    <?php echo $form->field($model, 'password_hash')->textInput(['maxlength' => true]) ?>

    <?php echo $form->field($model, 'fcm_token')->textInput(['maxlength' => true]) ?>

    <?php echo $form->field($model, 'email')->textInput(['maxlength' => true]) ?>

    <?php echo $form->field($model, 'email_status')->dropDownList([ 'not_verified' => 'Not verified', 'verified' => 'Verified', ], ['prompt' => '']) ?>

    <?php echo $form->field($model, 'account_status')->dropDownList([ 'normal' => 'Normal', 'suspended' => 'Suspended', 'exceed_max_login_attempt' => 'Exceed max login attempt', ], ['prompt' => '']) ?>

    <?php echo $form->field($model, 'suspicious_flag')->dropDownList([ 'true' => 'True', 'false' => 'False', ], ['prompt' => '']) ?>

    <?php echo $form->field($model, 'auth_key')->textInput(['maxlength' => true]) ?>

    <?php echo $form->field($model, 'access_token')->textInput(['maxlength' => true]) ?>

    <?php echo $form->field($model, 'notes')->textarea(['rows' => 6]) ?>

    <?php echo $form->field($model, 'status')->dropDownList([ 'enabled' => 'Enabled', 'disabled' => 'Disabled', ], ['prompt' => '']) ?>

    <?php echo $form->field($model, 'created_at')->textInput() ?>

    <?php echo $form->field($model, 'created_by')->textInput() ?>

    <?php echo $form->field($model, 'updated_at')->textInput() ?>

    <?php echo $form->field($model, 'updated_by')->textInput() ?>

    <?php echo $form->field($model, 'login_at')->textInput() ?>

    <?php echo $form->field($model, 'login_attempt')->textInput() ?>

    <div class="form-group">
        <?php echo Html::submitButton($model->isNewRecord ? Yii::t('backend', 'Create') : Yii::t('backend', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
