<?php 

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\User;
use common\models\fcm\SysFcmMessage;
use common\models\SysFcmGroup;
use common\models\SysFcmGroupUser;
use backend\widgets\TabMenuFcmWidget;
/* @var $this yii\web\View */
/* @var $model common\models\FcmToken */
/* @var $form ActiveForm */
$this->title = 'Send Push Notification Message To Group';

?>

<div class="firebase-cloud-messaging">
    <?php
        echo TabMenuFcmWidget::widget(['page'=>"group"]);
    ?>

    <?php $form = ActiveForm::begin(); ?>

        <?php
           // $list = ["name"=>"iphone_winners (1)", "name2"=>"iphone_winners (10)"]

            echo $form->field($model, 'to')->dropDownList(SysFcmGroupUser::groupNameAndNumberOfUser()); 
        ?>
        <?= Html::a('Manage User Group', ['sys-fcm-group/index'], ['class' => 'btn btn-success']) ?>
        <?php echo "<br><br>"; ?>
        <?= $form->field($model, 'title')->hint("(Maximum 128 characters)") ?>
        <?= $form->field($model, 'body')->textarea(['rows' => 6])->hint("(Maximum 256 characters)") ?>
        <?php 

            if (Yii::$app->user->can(User::ROLE_ADMINISTRATOR)) {
                $h = "[Normal message] - Save message into notification inbox & show push notification.<br>
                [Silent message] - Save message into notification inbox.<br>
                [System Message] - Show alert.<br>
                [Expire User Login Token] - Force logout targeted user(s).<br>";

                echo $form->field($model, 'action')->dropDownList(SysFcmMessage::fcmActionsAdmin())->hint($h);
            } else {

                $h = "[Normal message] - Save message into notification inbox & show push notification.<br>
                [Silent message] - Save message into notification inbox.<br>";

                echo $form->field($model, 'action')->dropDownList(SysFcmMessage::fcmActionsManager())->hint($h);
                //$form->field($model, 'action')->textInput(['placeholder' => "inbox/inbox_silent/system"]);
            }
        ?>
        <center><b>(Optional) External Web URL In Notification</b></center>
        <hr class='smallpadding'>
        <?= $form->field($model, 'link_url')->textInput(['placeholder' => 'http://www.abc.com'])->hint("(MUST INCLUDE 'http://' or 'https://') <b>ONLY APPLICABLE TO 'NORMAL MESSAGE'</b>") ?>
        <?= $form->field($model, 'link_desc')->textInput(['placeholder' => 'Tap here to find out more!'])->hint("(Maximum 128 characters)") ?>
    
        <div class="form-group">
            <?php 
                echo Html::submitButton('Send', [
                    'class' => 'btn btn-primary', 
                    'data' => [
                        'confirm' => 'Are you sure you want to send Push Message to group',
                    ]
                ]); 
            ?>
        </div>
    <?php ActiveForm::end(); ?>

</div><!-- firebase-cloud-messaging -->