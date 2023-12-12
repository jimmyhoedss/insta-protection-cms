<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\models\User;
use common\components\Utility;
use yii\captcha\Captcha;


/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \backend\models\LoginForm */

$this->title = Yii::t('dashboard', 'Sign In');
$this->params['breadcrumbs'][] = $this->title;
$this->params['body-class'] = 'login-page';

?>
<!-- <p>use https</p> -->

<div class="login-box" style="margin-bottom: 0; margin-top: 1%;">
    <div class="login-logo">
	<img alt="" class="logo-ip photo" src="/img/logo-ip.png">
	<?php echo Html::encode($this->title) ?>
    </div><!-- /.login-logo -->
    <div class="login-box-body">
        <?php $form = ActiveForm::begin([
            'id' => 'login-form',            
            'fieldConfig' => [
                'options'=>[
                    'style'=>'margin-bottom:7.5px;'
                ],
                'labelOptions' => ['class' => 'col-sm-12 col-form-label', 'style'=>'padding-top:3px;'],
                'template' => '<div class="col">
                                    {label}
                                    <div class="col-sm-12">{input}{hint}{error}</div>
                                </div>',
            ],
            'enableClientValidation' => false,
        ]); ?>
        <div class="body">
            <?php 
                echo $form->field($model, 'mobile_calling_code')->dropDownList(User::getMobileCallingCode())->label(Yii::t('dashboard','Country'));
                echo $form->field($model, 'mobile_number')->textInput(['autofocus' => true])->label(Yii::t('dashboard','Mobile'));
                echo $form->field($model, 'flag_method')->widget(\kartik\widgets\SwitchInput::classname(), [
                    'pluginOptions' => [
                        'size' => 'small',
                        'onColor' => 'warning',
                        'offColor' => 'warning',
                        'offText' => Yii::t('dashboard','SMS'),
                        'onText' => Yii::t('dashboard','Email'),
                        'handleWidth' => 40,
                    ]
                ])->label(Yii::t('dashboard','Mode'));
                echo $form->field($model, 'region_id', ['template'=>'{input}'])->hiddenInput(['value'=>'SG'])->label(false);
                echo $form->field($model, 'reCaptcha')->widget(Captcha::classname(), [
                  //configure additional widget properties here
                  'captchaAction' => ['site/captcha'],
                  'template' => '
                        <div class="captcha-container">
                            <div class="captcha-img">
                                {image}
                            </div>
                            <div class="captcha-input">
                                {input}
                            </div>
                        </div>',
                  // 'options' => ['class' => 'form-control'],
                  
              ])->label(Yii::t('dashboard',"Captcha"));
            ?>
        </div>
        <div class="footer">
            <?php echo Html::submitButton(Yii::t('dashboard', 'Sign me in'), [
                'class' => 'btn btn-primary btn-flat btn-block',
                'name' => 'login-button'
            ]) ?>
        </div>
        <?php ActiveForm::end() ?>
    </div>

</div>