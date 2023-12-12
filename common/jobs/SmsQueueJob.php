<?php

namespace common\jobs;

use Yii;
use yii\helpers\Url;
use yii\base\BaseObject;
use common\commands\SendSmsCommand;

class SmsQueueJob extends BaseObject implements \yii\queue\JobInterface
{
    public $mobileNumber;
    public $message;

    public function execute($queue)
    {
        Yii::$app->commandBus->handle(new SendSmsCommand([
            'mobileNumber' => $this->mobileNumber,
            'message' => $this->message,
        ]));
    }
}

