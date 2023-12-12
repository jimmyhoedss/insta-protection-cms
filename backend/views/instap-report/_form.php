<?php

use yii\helpers\Html;
// use yii\bootstrap\ActiveForm;
use kartik\form\ActiveForm;
use kartik\export\ExportMenu;
use kartik\daterange\DateRangePicker;
use common\models\InstapReport;


?>

<div class="instap-report-form">

    <?php 

    	$list = InstapReport::reportTypes();

    	$form = ActiveForm::begin([]);

	    echo $form->errorSummary($model);

	    echo $form->field($model, 'type')->dropDownList($list, ['prompt' => '']);


	    // echo $form->field($model, 'date_end')->textInput();

	    echo $form->field($model, 'dateRange', [
			    'addon'=>['prepend'=>['content'=>'<i class="glyphicon glyphicon-calendar"></i>']],
			    'options'=>['class'=>'drp-container form-group'],
			])->widget(DateRangePicker::classname(), [
			    'useWithAddon'=>true,
			    'convertFormat'=>true,
			    'options' => ['autocomplete' => 'off'],
				'startAttribute' => 'date_start',
				'endAttribute' => 'date_end',
				'startInputOptions'=> ['value' => Yii::$app->formatter->asDate($model->date_start)],
				'endInputOptions'=> ['value' => Yii::$app->formatter->asDate($model->date_end)],
				'pluginOptions'=>[
				'locale'=>['format' => 'd M Y'],
					]
				]);

	?>
    <div class="form-group">
        <?php echo Html::submitButton(Yii::t('backend', 'Generate'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
