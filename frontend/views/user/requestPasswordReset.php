<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\form\PasswordResetRequestForm;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $model \frontend\modules\user\models\PasswordResetRequestForm */

$this->title =  Yii::t('frontend', 'Forgot password');
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-request-password-reset">
    <div class="container container-sm">
        <div class="overview">
                <h3 class="menu-title"><?php echo Html::encode($this->title) ?></h3>
                <div class="text-left">Enter your email to reset your password.</div>
                <?php $form = ActiveForm::begin(['id' => 'request-password-reset-form']); ?>
                <br>
                <?php echo $form->field($model, 'email')->label(false) ?>
                
                <br>
                <div class="form-group text-center">
                    <?php echo Html::submitButton('Send', ['class' => 'btn submit-btn btn-custom']) ?>
                </div>
                <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
