<?php

namespace common\jobs;

use Yii;
use yii\queue\RetryableJobInterface;
use yii\base\BaseObject;
use yii\web\ServerErrorHttpException;
use trntv\bus\interfaces\SelfHandlingCommand;
use common\models\fcm\SysFcmMessage;
use common\models\fcm\SysFcmMessageError;
use common\models\fcm\PushNotification;
use common\commands\SendFcmCommand;


class FcmQueueJob extends BaseObject implements RetryableJobInterface
{   
    public $notification;

    public function execute($queue) {

        $notif = array (
            'title' => $this->notification->title,
            'body'  => $this->notification->summary,
            'icon'  => 'myicon',/*Default Icon*/
            'vibrate'   => 1,
            'sound' => 1,
            'badge' => 1
        );

        // instead of selecting what value to put in data, remove the unwanted values and set the rest as $data
        $data = $this->notification;

        $fields = array (
            'to' => $this->notification->fcm_token,
            'priority'=>'high',
            'notification'  => $notif,
            'data' => $data
        );
        
        unset($data->fcm_token);
        unset($data->recipient_type);
        unset($data->hasSetRecipient);

        if($this->notification->notification_type == PushNotification::NOTIFICATION_TYPE_ACTION) {
            unset($fields->notification);
        }
    
        $headers = array (
            'Authorization: key=' . env('FCM_SERVER_KEY'),
            'Content-Type: application/json'
        );

        Yii::$app->commandBus->handle(new SendFcmCommand([
            'headers' => $headers,            
            'fields' => $fields
        ]));
        
    }

    public function getTtr(){
        $ttr = Yii::$app->queue->ttr;
        return $ttr;
    }

    public function canRetry($attempt, $error){
        echo "...retrying, attempt no: " . $attempt . "\n";
        $maxAttempt = Yii::$app->queue->attempts;
        if($attempt == $maxAttempt){
            echo "...failed to send fcm\n";
            $m = new SysFcmMessageError();
            $m->logError($this->notification->type, $this->notification->fcm_token, $this->notification->title, $this->notification->summary);
        }
        return ($attempt < $maxAttempt);// && ($error instanceof TemporaryException);
    }

}