<?php 

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\User;
use common\models\fcm\SysFcmMessage;
use backend\widgets\TabMenuFcmWidget;

if ($type == SysFcmMessage::TYPE_INDIVIDUAL) {
    $this->title = 'Send Push Notification Message To: <b>' . utf8_decode($userProfile->nickname) . "</b>";
    $user_id = $userProfile->user_id;
} else if ($type == SysFcmMessage::TYPE_BROADCAST) {
    $this->title = 'Send Push Notification Message To All Users';
}

?>

<div class="firebase-cloud-messaging">
    <?php
        //echo TabMenuFcmWidget::widget(['page'=>"individual"]);
    ?>

    <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'title')->hint("(Maximum 128 characters)") ?>
        <?= $form->field($model, 'body')->textarea(['rows' => 6])->hint("(Maximum 256 characters)") ?>
        <?php 

            if (Yii::$app->user->can(User::ROLE_ADMINISTRATOR)) {
                $h = "[Normal message] - Save message into notification inbox & show push notification.<br>
                [Silent message] - Save message into notification inbox.<br>
                [System Message] - Show alert.<br>
                [Expire User Login Token] - Force logout targeted user(s).<br>";

                echo $form->field($model, 'action')->dropDownList(SysFcmMessage::fcmActionsAdmin(), ['id' => 'fcm-action'])->hint($h);
            } else {

                $h = "[Normal message] - Save message into notification inbox & show push notification.<br>
                [Silent message] - Save message into notification inbox.<br>";

                echo $form->field($model, 'action')->dropDownList(SysFcmMessage::fcmActionsManager(), ['id' => 'fcm-action'])->hint($h);
                //$form->field($model, 'action')->textInput(['placeholder' => "inbox/inbox_silent/system"]);
            }
        ?>
        
        <div class="form-group">
            <?php 
                if ($type == SysFcmMessage::TYPE_INDIVIDUAL) {                    
                    echo Html::submitButton('Send', [
                        'class' => 'btn btn-primary', 
                        'data' => [
                            'confirm' => 'Are you sure you want to send Push Message to ['.utf8_decode($userProfile->nickname).']?',
                        ]
                    ]); 
                } else if ($type == SysFcmMessage::TYPE_BROADCAST) {                   
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
$script = "";
if ($type == SysFcmMessage::TYPE_INDIVIDUAL) {
    
$script = <<< JS

$('#fcm-action').on('change', function (e) {
    var val = $('#fcm-action').val();
    window.location.href = "/sys-fcm-message/send-individual?user_id=$user_id&action=" + val;
});

JS;
} else if ($type == SysFcmMessage::TYPE_BROADCAST) {
    
$script = <<< JS

$('#fcm-action').on('change', function (e) {
    var val = $('#fcm-action').val();
    window.location.href = "/sys-fcm-message/broadcast?action=" + val;
});


JS;
}

$this->registerJs($script);

?>