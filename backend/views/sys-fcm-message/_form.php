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

$('#fcm-type').on('change', function (e) {
    var val = $('#fcm-type').val();
    window.location.href = "/sys-fcm-message/$view"+"type=" + val;
});

JS;

$this->registerJs($script);

?>