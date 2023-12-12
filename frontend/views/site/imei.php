<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
?>

<div style="display: flex; justify-content: center;">
 
<div class="container-fluid">
  <div class="row">
    <div class="col" >
    	<?php $form = ActiveForm::begin(); ?>
	    <?= $form->field($model, 'sp_imei')->textInput(['placeholder' => Yii::t('frontend', 'Enter your Imei number')]) ?>
	    <div class="form-group">
	        <?= Html::submitButton(Yii::t('backend', 'Enter'), ['class' => 'btn btn-success']) ?>
	    </div>
    	<?php ActiveForm::end(); ?>
	</div>

    <div class="col">
	<?php 
		echo $msg;
	?>
	</div>
  </div>
</div>

	
</div>