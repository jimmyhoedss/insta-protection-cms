<?php

namespace common\jobs;

use Yii;
use yii\base\BaseObject;
use common\models\TimelineEvent;
use common\models\User;
use common\models\SysFcmMessage;
use common\commands\SendEmailCommand;
use yii\helpers\Url;
use yii\swiftmailer\Message;

class TestQueueJob extends BaseObject implements \yii\queue\JobInterface
{
    public $param1;
    public $param2;

    public function execute($queue)
    {
        echo "test queue job runnning ... ";
        echo "param1:" . $this->param1 . ", param2: " . $this->param2;
        echo "\n";

    }


    
}
