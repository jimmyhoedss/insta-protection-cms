<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\models\User;
use common\components\Utility;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \backend\models\LoginForm */

$this->title = Yii::t('backend', 'Sign In');
$this->params['breadcrumbs'][] = $this->title;
$this->params['body-class'] = 'login-page';

?>
<p>use https</p>

<div class="login-box">
    <div class="login-logo">
        <?php echo Html::encode($this->title) ?>
    </div><!-- /.login-logo -->
    <div class="header"></div>
    <div class="login-box-body">
        <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
        <div class="body">
            <?php 
            echo $form->field($model, 'region_id')->hiddenInput(['value'=>'SG'])->label(false);
            echo $form->field($model, 'mobile_calling_code')->dropDownList(User::getMobileCallingCode());
            echo $form->field($model, 'mobile_number')->textInput(['autofocus' => true])
            /*echo $form->field($model, 'rememberMe')->checkbox(['class'=>'simple'])
            echo $form->field($model, 'form_step')->hiddenInput(['value'=>$model->form_step])->label(false);*/
            ?>
        </div>
        <div class="footer">
            <?php echo Html::submitButton(Yii::t('backend', 'Sign me in'), [
                'class' => 'btn btn-primary btn-flat btn-block',
                'name' => 'login-button'
            ]) ?>
        </div>
        <?php ActiveForm::end() ?>
    </div>

</div>