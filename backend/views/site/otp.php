<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \backend\models\LoginForm */

$this->title = Yii::t('backend', 'One-Time Pin');    

$this->params['breadcrumbs'][] = $this->title;
$this->params['body-class'] = 'login-page';
?>
<div class="login-box">
    <div class="login-logo">
        <?php echo Html::encode($this->title) ?>
    </div><!-- /.login-logo -->
    <div class="header"></div>
    <div class="login-box-body">
        <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
        <p><center><?=Yii::t("backend","A 6 digit One-time PIN has been sent to you.")?><br><?=Yii::t("backend","Please check your SMS inbox.")?></center></p><p><center><b><?=Yii::t("backend","Enter the code to continue")?></b></center></p>
        <div class="body">
            <?php echo $form->field($model, 'mobile_number_full')->textInput(['readonly'=> true])->label(false); ?> 
            <?php echo $form->field($model, 'token')->textInput(['autofocus' => true]) ?>            
        </div>
        <div class="footer">
            <?php echo Html::submitButton(Yii::t('backend', 'Sign me in'), [
                'class' => 'btn btn-primary btn-block btn-flat',
                'name' => 'login-button'
            ]) ?>
        </div>
        <?php ActiveForm::end() ?>
        <hr>
        <?=Yii::t("backend","Did not receive your OTP?")?>
        <span id="timer">&nbsp;<i class="fa fa-clock"></i>&nbsp;00:<span id="countdown-time">30</span></span>
        <?php
            $otpForm = new \common\models\form\OtpForm;
            $otpForm->mobile_number_full = $model->mobile_number_full;
            $resendForm = ActiveForm::begin([
                'id' => 'resendForm',
                'action' => '/resend-otp',
                // 'enableAjaxValidation' => true,
                // 'validationUrl' => '/exhibitor/like-validate',
            ]); 

            echo $resendForm->field($otpForm, 'mobile_number_full')->hiddenInput()->label(false);
            echo Html::submitButton(Yii::t('backend', 'Resend OTP'), [
                'class' => 'btn btn-default btn-block btn-flat',
                'name' => 'resend-button'
            ]);
            // $resendForm->end();
            ActiveForm::end();
        ?>
        <p id="resend-info"></p>
    </div>
</div>


<?php
$script = <<< JS

$('form#resendForm button').prop('disabled', true);
var timer = setInterval(countdown, 1000);
$('#timer').fadeIn();

function countdown() {
    let currentTime = $('#countdown-time').text();
    if(currentTime < 1){
        $('form#resendForm button').prop('disabled', false);
        clearInterval(timer);
        $('#timer').fadeOut();
    } else {
        $('#countdown-time').text(currentTime-1)
    }
}


$('#resendForm').on('beforeSubmit', function() {
    var data = $('#resendForm').serialize();
    $('#countdown-time').text('30');
    $('form#resendForm button').prop('disabled', true);
    timer = setInterval(countdown, 1000);
    $('#timer').fadeIn();
    $.ajax({
        url: $('#resendForm').attr('action'),
        type: 'POST',
        data: data,
        success: function (data) {
            // Implement successful
            console.log(data)
            if(data.success){
                $('#resend-info').addClass('text-success');
                $('#resend-info').text('Successfully sent new OTP.');
                setTimeout(()=>{
                    $('#resend-info').removeClass('text-danger');
                    $('#resend-info').removeClass('text-success');
                    $('#resend-info').html('');
                }, 3000)

            } else {
                let errors = Object.values(data.errors);

                let msg = '';
                for (err of errors) {
                    msg += err;
                    msg += '<br>';
                }
                $('#resend-info').addClass('text-danger');
                $('#resend-info').html(msg);
                setTimeout(()=>{
                    $('#resend-info').removeClass('text-danger');
                    $('#resend-info').removeClass('text-success');
                    $('#resend-info').html('');
                }, 3000)
            }
        },
        error: function(jqXHR, errMsg) {
            alert(errMsg);
        }
     });
     return false; // prevent default submit
});

JS;
$this->registerJs($script);


?>