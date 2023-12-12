<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\UserCase */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="user-case-form">

    <?php $form = ActiveForm::begin(); ?>

    <?php echo $form->errorSummary($model); ?>

    <?php echo $form->field($model, 'id')->textInput() ?>

    <?php echo $form->field($model, 'plan_pool_id')->textInput() ?>

    <?php echo $form->field($model, 'user_id')->textInput() ?>

    <?php echo $form->field($model, 'case_type')->dropDownList([ 'claim' => 'Claim', ], ['prompt' => '']) ?>

    <?php echo $form->field($model, 'description')->textarea(['rows' => 6]) ?>

   <!--  <?php echo $form->field($model, 'case_status')->dropDownList([ 'pending' => 'Pending', 'require_clarification' => 'Require clarification', 'internal_approve_for_service_provider' => 'Internal approve for service provider', 'service_provider_picked_up' => 'Service provider picked up', 'internal_pending_for_insurer' => 'Internal pending for insurer', 'internal_approved_for_claim' => 'Internal approved for claim', 'repaired' => 'Repaired', 'delivered' => 'Delivered', 'rejected' => 'Rejected', 'closed' => 'Closed', ], ['prompt' => '']) ?> -->

    <?php echo $form->field($model, 'notes')->textarea(['rows' => 6]) ?>

    <?php echo $form->field($model, 'status')->dropDownList([ 'enabled' => 'Enabled', 'disabled' => 'Disabled', ], ['prompt' => '']) ?>


    <div class="form-group">
        <?php echo Html::submitButton($model->isNewRecord ? Yii::t('backend', 'Create') : Yii::t('backend', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
