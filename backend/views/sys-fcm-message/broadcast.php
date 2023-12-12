<?php 

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\User;
use common\models\fcm\SysFcmMessage;
use common\models\fcm\PushNotification;
use backend\widgets\TabMenuFcmWidget;
use yii\web\NotFoundHttpException;
/* @var $this yii\web\View */
/* @var $model common\models\FcmToken */
/* @var $form ActiveForm */
$this->title = 'Send Push Notification Message To All Users';

?>

<div class="firebase-cloud-messaging">
    <?php
        $form = "";
        switch ($type) {
            case PushNotification::TYPE_INBOX:
                //title
                //summary
                //body
                $form = "_formTitleSummaryBody";
                break;
            
            case PushNotification::TYPE_INBOX_HYPERLINK:
                //title
                //summary
                //body
                //hyperlink_url
                //hyperlin_text
                $form = "_formTitleSummaryBodyHyperlink";
                break;
            
            case PushNotification::TYPE_INBOX_BANNER_HYPERLINK:
                //title
                //summary
                //body
                //hyperlink_url
                //hyperlin_text
                //banner_url
                $form = "_formTitleSummaryBodyBannerHyperlink";
                break;
            
            case PushNotification::TYPE_INBOX_BANNER:
                //title
                //summary
                //body
                //banner_url
                $form = "_formTitleSummaryBodyBanner";
                break;
            
            case PushNotification::TYPE_ACTION_ALERT:
                //title
                //summary
                $form = "_formTitleSummary";
                break;
            
            case PushNotification::TYPE_ACTION_LOGOUT_SILENT:
                $form = "_form";
                break;
            
            case PushNotification::TYPE_ACTION_LOGOUT_ALERT:
                //title
                //summary
                $form = "_formTitleSummary";
                break;
            
            case PushNotification::TYPE_ACTION_DAILY_RESYNC:
                $form = "_form";
                break;

            default:
                throw new NotFoundHttpException('The requested page does not exist.');
                break;
        }
        echo $this->render($form, [
            'model' => $model,
            'recipient_type' => PushNotification::RECIPIENT_TYPE_TOPIC,
            'recipient'=> PushNotification::BROADCAST_ID
        ]);
    ?>


</div><!-- firebase-cloud-messaging -->