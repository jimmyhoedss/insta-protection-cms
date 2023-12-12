<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\SysFeedback */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="sys-feedback-form">

    <?php $form = ActiveForm::begin(); ?>

    <?php 
        echo $form->field($model, 'name')->textInput(['maxlength' => true,'readonly'=> true]);
        echo $form->field($model, 'email')->textInput(['maxlength' => true,'readonly'=> true]);
        echo $form->field($model, 'subject')->dropDownList([ 'general' => 'General', 'technical' => 'Technical', ], ['disabled' => true]);
        echo $form->field($model, 'message')->textarea(['rows' => 6,'readonly'=> true]);
        echo $form->field($model, 'status')->dropDownList([ 'enabled' => 'Enabled', 'disabled' => 'Disabled', ]);
        echo "<hr>";
        echo $form->field($model, 'notes')->textarea(['rows' => 6, 'placeholder'=> 'Enter follow up notes for this feedback.'])->label("Manager's notes");
    ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
