<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\web\JsExpression;
use trntv\filekit\widget\Upload;
use common\models\SysRegion;
// use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use common\widgets\MyUpload\MyUpload;

/* @var $this yii\web\View */
/* @var $model common\models\InstapPromotion */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="instap-banner-form">

    <?php 
        $form = ActiveForm::begin(); 

        $html = $form->field($model, 'title')->textInput(['maxlength' => true]);

        /*$html .=  $form->field($model, 'region_id')->widget(Select2::classname(), [
                'data' => ArrayHelper::map(SysRegion::find()->all(), 'id', 'name'),
                'options' => ['placeholder' => 'Select Region ...'],
                'pluginOptions' => [
                    'allowClear' => true ],
                ])->label('Region');*/

        $html .= $form->field($model, 'region_id', ['template' => '{input}'])->hiddenInput(['value' => Yii::$app->session->get('region_id')]);
        
        //LOYNOTE: Should we have a click thru url field?
        
        $html .=  $form->field($model, 'webview_url')->textInput()->hint("Leave blank for none."); 

        $html .=  $form->field($model, 'description')->textarea(['rows' => 6]); 



        $html .=  $form->field($model, 'thumbnail')->widget(
            MyUpload::className(),
            [
                'url' => ['/file-storage/upload'],
                'maxFileSize' => 5000000, // 5 MiB
                'uploadPath' => 'media/banner',
                'acceptFileTypes' => new JsExpression('/(\.|\/)(jpe?g|png)$/i'),
            ]); 

        $html .= $form->field($model, 'status')->dropDownList([ 'enabled' => 'Enabled', 'disabled' => 'Disabled', ], ['prompt' => '']); 

        echo $html;
    ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('backend', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
