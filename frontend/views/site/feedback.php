<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\authclient\widgets\AuthChoiceAsset;
use frontend\assets\LoginAsset;
use yii\captcha\Captcha;
use common\models\form\FeedbackForm;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $model \frontend\modules\user\models\SignupForm */

$this->title = Yii::t('frontend', 'Feedback');
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-feedback">

    <div class="container container-sm">
        <div class="overview">
            <br>
            <h3 class="menu-title menu-title-tight "><?php echo Html::encode($this->title) ?></h3>
            <p>Submit your feedback here.</p>

            <div class="form-holder">
            <?php $form = ActiveForm::begin(['id' => 'form-feedback']); ?>

                    <div class="form-group">

                <?php 
                //echo $form->field($model, 'username');
                ?>
                <?php 
                    echo $form->field($model, 'subject')->dropDownList(FeedbackForm::subjects(), ['prompt' => 'Select type of enquiry'])->label(false);
                    echo "<div class='row'><div class='col-sm-6'>";
                    echo $form->field($model, 'name')->textInput(['placeholder' => $model->getAttributeLabel('name')])->label(false);
                    echo "</div><div class='col-sm-6'>";
                    echo $form->field($model, 'email')->textInput(['placeholder' => $model->getAttributeLabel('email')])->label(false);
                    echo "</div></div>";                    
                    echo $form->field($model, 'message')->textarea(['rows' => 6])->hint("(Maximum 1000 characters)");

                    //echo $form->field($model, 'captcha')->widget(Captcha::className());
                    echo "<div class='text-left'>";
                    echo $form->field($model, 'reCaptcha')->widget(\himiklab\yii2\recaptcha\ReCaptcha::className())->label("");
                    echo "</div>";
                ?>
                
                
                <br>
                <div class="form-group text-center">
                    <?php echo Html::submitButton(Yii::t('frontend', 'Submit'), ['class' => 'btn submit-btn btn-custom ', 'name' => 'signup-button']) ?>
                </div>
                </div>

                
            <?php ActiveForm::end(); ?>
            </div>
                    <br>
                <p class="bottom-text small text-center">
                    <?php
                    echo Yii::t('frontend', 'By proceeding, I agree that you can collect, use and disclose the information provided by me in accordance with your <a class="nobr" href="{link}">Privacy Policy</a> which I have read and understand.', [
                        'link'=>yii\helpers\Url::to(['/privacy'])
                    ]) ?>
                </p>
        </div>
    </div>
</div>

