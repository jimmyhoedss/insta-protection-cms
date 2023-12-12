<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\bootstrap\ActiveForm;
use yii\web\JsExpression;
use trntv\filekit\widget\Upload;
use common\models\DealerCompany;
use common\models\UserProfile;
use common\models\InstapPlanPool;
use common\models\UserCase;
use common\models\QcdClaimRegistration;
use common\models\QcdRepairCentre;
use dashboard\widgets\accordionWidget;
use kartik\widgets\DateTimePicker;


/* @var $this yii\web\View */
/* @var $model common\models\UserCase */

$this->title = Yii::t('dashboard', 'Submit clarification');
$this->params['breadcrumbs'][] = ['label' => $model->policy_number, 'url' => ['view', 'id'=>$model->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-case-create">

    <h4 class="sub-title"><?=Yii::t('dashboard','Policy Details')?></h4>
    <?php echo $model->getPolicyDetailLayout(); ?>

    <hr>
    <h4 class="sub-title"><?=Yii::t('dashboard','Message from InstaProtection')?></h4>
    <div class="jumbotron">
        <p style="font-size: 15px; padding-left: 15px;"><?= $notes ?></p>
    </div>
    <hr>
    <div class="user-case-form">

        <?php 
            $html = "";

            $form = ActiveForm::begin(['enableClientValidation'=>false, 'options' => ['enctype' => 'multipart/form-data']]); 

            $html .=  $form->field($m, 'description')->textarea(['rows' => 6, 'placeholder'=>"", 'maxlength'=>500])->hint("");

            $html .=  $form->field($m, 'image_file[]')->fileInput(['multiple' => true, 'accept' => 'image/*'])->label(Yii::t('dashboard',"Upload Photo"))->hint(Yii::t('dashboard',"Max 5 images")); 

            $html .= $form->field($m, 'plan_pool_id')->hiddenInput()->label("");

            $html .= Html::tag('div', Html::submitButton(Yii::t('dashboard', 'Submit'), [
                'class' => 'btn btn-success',
                'data' => [
                    'confirm' => Yii::t('dashboard', 'Are you sure you want to submit?'),
                    'method' => 'post',
                ],
            ]), ['class' => 'form-group']);

            echo $html;
            ActiveForm::end();
        ?>
    </div>
</div>

<?php

$script = <<< JS

JS;
$this->registerJs($script);

?>
