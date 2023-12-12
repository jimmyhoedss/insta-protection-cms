<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\web\JsExpression;
use trntv\filekit\widget\Upload;
use common\models\SysRegion;
use common\models\InstapPlan;
// use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use common\widgets\MyUpload\MyUpload;

/* @var $this yii\web\View */
/* @var $model common\models\InstapPlan */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="instap-plan-form">

    <?php 

        $category = InstapPlan::allPlanCategories(); 
        $tier = InstapPlan::allPlanTier(); 
        unset($tier[InstapPlan::ALL_TIER]);//remove "all_tier" from array

        $form = ActiveForm::begin(); 
        $html = $form->field($model, 'sku')->textInput(['maxlength' => true]); 
        $html .= $form->field($model, 'category')->dropDownList($category, ['prompt' => ['text'=> '--Select--', 'options'=> ['disabled' => true, 'selected' => true]]]);
        $html .= $form->field($model, 'tier')->dropDownList($tier, ['prompt' => ['text'=> '--Select--', 'options'=> ['disabled' => true, 'selected' => true]]]);

        /*$html .= $form->field($model, 'region_id')->widget(Select2::classname(), [
                'data' => ArrayHelper::map(SysRegion::find()->all(), 'id', 'name'),
                'options' => ['placeholder' => 'Select Region ...'],
                'pluginOptions' => [
                    'allowClear' => true ],
                ])->label('Region');*/

        $html .= $form->field($model, 'region_id', ['template' => '{input}'])->hiddenInput(['value' => Yii::$app->session->get('region_id')]);

        
        $html .= $form->field($model, 'master_policy_number')->textInput(['maxlength' => true]);

        $html .= $form->field($model, 'name')->textInput(['maxlength' => true]);

        $html .= $form->field($model, 'description')->textarea(['rows' => 6]); 

        // $html .= $form->field($model, 'webview_url')->textInput()->label("Webview Holder Url")->hint("This url will contain the policy details of the plan."); 

        $html .=  $form->field($model, 'thumbnail')->widget(
            MyUpload::className(),
            [
                'url' => ['/file-storage/upload'],
                'maxFileSize' => 5000000, // 5 MB
                'uploadPath' => 'media/plan',
                'acceptFileTypes' => new JsExpression('/(\.|\/)(jpe?g|png)$/i'),
                // 'maxNumberOfFiles' => 3,
            ]
        );

        $html .=  $form->field($model, 'pdf')->widget(
            MyUpload::className(),
            [
                'url' => ['/file-storage/upload-pdf'],
                'maxFileSize' => 8000000, // 8 MB
                'uploadPath' => 'media/policy_detail',
                'acceptFileTypes' => new JsExpression('/(\.|\/)(pdf)$/i'),
                // 'maxNumberOfFiles' => 3,
            ]
        );

        $html .= $form->field($model, 'coverage_period')->textInput()->label("Coverage Period (Months)"); 

        $html .= $form->field($model, 'retail_price')->textInput(); 
        $html .= $form->field($model, 'premium_price')->textInput(); 
        $html .= $form->field($model, 'dealer_price')->textInput(); 

        $html .= $form->field($model, 'status')->dropDownList([ 'enabled' => 'Enabled', 'disabled' => 'Disabled', ], ['prompt' => '']); 

        echo $html;

    ?>



    <div class="form-group">
        <?= Html::submitButton(Yii::t('backend', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
