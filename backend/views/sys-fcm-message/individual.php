<?php 

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use common\models\fcm\SysFcmMessage;
use backend\widgets\TabMenuFcmWidget;
/* @var $this yii\web\View */
/* @var $model common\models\FcmToken */
/* @var $form ActiveForm */
$this->title = 'Send Push Message To Individual';

$link = Url::to(["user/index"]);

?>

<div class="firebase-cloud-messaging">
    <?php
        echo TabMenuFcmWidget::widget(['page'=>"individual"]);
    ?>
    <br><br><br><br>
    <p class="text-center">To send Push Message to individual user, please navigate to <a href="<?php echo $link; ?>">users</a> and click on the <a><span class="glyphicon glyphicon-envelope"></span></a> icon. </p>
    <br><br><br><br>
    

</div><!-- firebase-cloud-messaging -->