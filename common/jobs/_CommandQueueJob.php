<?php

namespace common\jobs;

use Yii;
use yii\base\BaseObject;
use common\models\TimelineEvent;
use common\commands\SendEmailCommand;
use yii\helpers\Url;
use yii\swiftmailer\Message;


class CommandQueueJob extends BaseObject implements \yii\queue\JobInterface
{
	public $command;
    public $file;

    public function execute($queue)
    {
        $r =  Yii::$app->commandBus->handle($command);
        /*
        $message = new Message();
        $message->setTextBody("yeas yea eya ");
        $message->setFrom("loytheman@gmail.com");
        $message->setTo("loytheman@gmail.com");
        $message->setSubject("loy test");
        $message->send();
        */
    }
    
}
