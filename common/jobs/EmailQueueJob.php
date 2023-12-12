<?php

namespace common\jobs;

use Yii;
use yii\helpers\Url;
use yii\base\BaseObject;
use yii\swiftmailer\Message;
use yii\web\ServerErrorHttpException;
use common\models\TimelineEvent;
use common\models\User;
use common\models\SysSesTrace;
use common\models\SysSendMessageError;
use trntv\bus\interfaces\SelfHandlingCommand;
use common\commands\SendEmailCommand;

class EmailQueueJob extends BaseObject implements \yii\queue\JobInterface
{
    public $subject;
    public $view;
    public $to;
    public $cc; //set cc to multiple user , edit list from here Yii::$app->params['carbonCopyEmailList']
    public $params;
    public $language;

    public function execute($queue)
    {
        //use to switch btw different language
        if($this->language) {
            Yii::$app->language = $this->language;
        }
        Yii::$app->commandBus->handle(new SendEmailCommand([
            'subject' => $this->subject,
            'view' => $this->view,
            'to' => $this->to,
            'cc' => $this->cc,
            'params' => $this->params,
        ]));

        // $emailTrace = SysSesTrace::makeModel($this->to);
        // $emailTrace->save();
        //track email logic
        // Yii::$app->queue->on(Queue::EVENT_AFTER_ERROR, function (ErrorEvent $event) {
        //     if ($event->job instanceof SomeJob) {
        //         $event->retry = ($event->attempt < 5) && ($event->error instanceof TemporaryException);
        //          User::sendTelegramBotMessage("Failed to send verification Email");
        //         User::sendTelegramBotMessage(json_encode("testing"));
        //     }
        // });
    }
}


/*

Eddie NOTE: 
Swiftmailer has nothing to do with the actual delivery of the mail. 
It just hands things over to whatever SMTP server you specified, and it's that server that takes care of the delivery. 
You need to check the SMTP server's logs to see what happened to the mail. 
It may get stuck in the outgoing queue because the server's swamped. 
It may get stuck in a queue because the receiving end is unreachable or is using grey-listing, etc... 
Swiftmailer's job ends once it gets acknowledgement from the SMTP server that the mail's been queued.

tl;dr: 
1) Swiftmailer will always return true/1 when it has successfully handed things over to SMTP
2) If swiftmailer returns false/0, it means it didnt hand things over to SMTP. *NOT MAIL NOT DELIVERED*
3) The actual delivery status is handed by that SMTP server

ref: https://stackoverflow.com/questions/5768389/swift-mailer-delivery-status

*/