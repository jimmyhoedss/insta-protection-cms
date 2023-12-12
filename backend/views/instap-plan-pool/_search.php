<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\search\InstapPlanPoolSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="instap-plan-pool-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'plan_id') ?>

    <?= $form->field($model, 'dealer_company_id') ?>

    <?= $form->field($model, 'user_id') ?>

    <?= $form->field($model, 'region_id') ?>

    <?php // echo $form->field($model, 'plan_category') ?>

    <?php // echo $form->field($model, 'plan_sku') ?>

    <?php // echo $form->field($model, 'policy_number') ?>

    <?php // echo $form->field($model, 'plan_status') ?>

    <?php // echo $form->field($model, 'notes') ?>

    <?php // echo $form->field($model, 'coverage_start_at') ?>

    <?php // echo $form->field($model, 'coverage_end_at') ?>

    <?php // echo $form->field($model, 'status') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'created_by') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <?php // echo $form->field($model, 'updated_by') ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('backend', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('backend', 'Reset'), ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
