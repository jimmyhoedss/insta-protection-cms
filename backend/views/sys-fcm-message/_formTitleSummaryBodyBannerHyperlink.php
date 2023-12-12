<?php 

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\User;
use common\models\fcm\SysFcmMessage;
use common\models\fcm\PushNotification;
use backend\widgets\TabMenuFcmWidget;

?>

<div class="firebase-cloud-messaging">

    <?php $form = ActiveForm::begin(); ?>

        <?= $form->errorSummary($model) ?>
        <?= $form->field($model, 'title')->textInput(['id' => 'title'])->hint("For push notification <b>TITLE</b>(Maximum 64 characters)") ?>
        <?= $form->field($model, 'summary')->textInput(['id' => 'summary'])->hint("For push notification <b>SUMMARY</b>(Maximum 128 characters)") ?>
        <?= $form->field($model, 'body')->textarea(['rows' => 6, 'id' => 'body'])->hint("(Maximum 255 characters)") ?>
        <?= $form->field($model, 'hyperlink_text')->textInput(['value' => 'Tap here to find out more!', 'id' => 'hyperlink_text'])->hint("(Maximum 128 characters)") ?>
        <?= $form->field($model, 'hyperlink_url')->textInput(['placeholder' => 'http://www.abc.com', 'id' => 'hyperlink_url'])->hint('(must include "http://" or "https://")'); ?>
        <?= $form->field($model, 'banner_url')->textInput(['placeholder' => 'http://www.abc.com', 'id' => 'banner_url'])->hint('(must include "http://" or "https://")'); ?>
        <?= $form->field($model, 'type')->dropDownList(PushNotification::fcmTypes(), ['id' => 'fcm-type'])->hint(PushNotification::fcmTypeDescriptions()) ?>
        <?= $form->field($model, 'recipient_type')->hiddenInput(['value'=>$recipient_type])->label(false); ?>
        <?= $form->field($model, 'recipient')->hiddenInput(['value'=>$recipient])->label(false); ?>
    
        <div class="form-group">
            <?php 
                if ($recipient_type == PushNotification::RECIPIENT_TYPE_DEVICE) {                    
                    echo Html::submitButton('Send', [
                        'class' => 'btn btn-primary', 
                        'data' => [
                            'confirm' => 'Are you sure you want to send Push Message to ['.utf8_decode($userProfile->nickname).']?',
                        ]
                    ]); 
                } else if ($recipient_type == PushNotification::RECIPIENT_TYPE_TOPIC) {                   
                    echo Html::submitButton('Send', [
                        'class' => 'btn btn-primary', 
                        'data' => [
                            'confirm' => 'Are you sure you want to send Push Message to All Users?',
                        ]
                    ]);
                }
            ?>
        </div>
    <?php ActiveForm::end(); ?>

</div><!-- firebase-cloud-messaging -->

<?php

$view = $recipient_type == PushNotification::RECIPIENT_TYPE_DEVICE ? "send-individual?user_id=$recipient&" : "broadcast?";
$script = "";
    
$script = <<< JS

const defaultTitleHint = $("#title").siblings('.hint-block').html();
$("#title").on('keyup', function (e) {
    $("#title").siblings('.hint-block').html(defaultTitleHint + " Text count: " + $("#title").val().length) 
});

const defaultSummaryHint = $("#summary").siblings('.hint-block').html();
$("#summary").on('keyup', function (e) {
    $("#summary").siblings('.hint-block').html(defaultSummaryHint + " Text count: " + $("#summary").val().length) 
});

const defaultBodyHint = $("#body").siblings('.hint-block').html();
$("#body").on('keyup', function (e) {
    $("#body").siblings('.hint-block').html(defaultBodyHint + " Text count: " + $("#body").val().length) 
});

const defaultHyperlinkUrlHint = $("#hyperlink_url").siblings('.hint-block').html();
$("#hyperlink_url").on('keyup', function (e) {
    $("#hyperlink_url").siblings('.hint-block').html(defaultHyperlinkUrlHint + " Text count: " + $("#hyperlink_url").val().length) 
});

const defaultHyperlinkTextHint = $("#hyperlink_text").siblings('.hint-block').html();
$("#hyperlink_text").on('keyup', function (e) {
    $("#hyperlink_text").siblings('.hint-block').html(defaultHyperlinkTextHint + " Text count: " + $("#hyperlink_text").val().length) 
});

const defaultBannerUrlHint = $("#banner_url").siblings('.hint-block').html();
$("#banner_url").on('keyup', function (e) {
    $("#banner_url").siblings('.hint-block').html(defaultBannerUrlHint + " Text count: " + $("#banner_url").val().length) 
});

$('#fcm-type').on('change', function (e) {
    var val = $('#fcm-type').val();
    window.location.href = "/sys-fcm-message/$view"+"type=" + val;
});

JS;

$this->registerJs($script);

?>