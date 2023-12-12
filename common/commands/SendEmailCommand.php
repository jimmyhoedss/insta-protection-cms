<?php

namespace common\commands;

use yii\base\BaseObject;
use yii\swiftmailer\Message;
use common\models\TimelineEvent;
use common\models\SysSendMessageError;
use trntv\bus\interfaces\SelfHandlingCommand;
use yii\web\ServerErrorHttpException;
use Yii;

/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class SendEmailCommand extends BaseObject implements SelfHandlingCommand
{
    /**
     * @var mixed
     */
    public $from;
    /**
     * @var mixed
     */
    public $to;
    /**
     * @var array
     */
    public $cc;
    /**
     * @var string
     */
    public $subject;
    /**
     * @var string
     */
    public $view;
    /**
     * @var array
     */
    public $params;
    /**
     * @var string
     */
    public $body;
    /**
     * @var bool
     */
    public $html = true;

    /**
     * Command init
     */
    public function init()
    {
        $this->from = $this->from ?: [\Yii::$app->params['robotEmail'] => \Yii::$app->params['adminEmailName']];
        //->setFrom(['john@doe.com' => 'John Doe'])
    }

    /**
     * @return bool
     */
    public function isHtml()
    {
        return (bool) $this->html;
    }

    /**
     * @param \common\commands\SendEmailCommand $command
     * @return bool
     */
    public function handle($command)
    {
        echo "Sending email [" . $command->subject . "] " . $command->to;
        // echo "executing email queue";
        if (!$command->body) {
            $message = \Yii::$app->mailer->compose($command->view, $command->params);
        } else {
            $message = new Message();
            if ($command->isHtml()) {
                $message->setHtmlBody($command->body);
            } else {
                $message->setTextBody($command->body);
            }
        }
        $message->setFrom($command->from);
        $message->setTo($command->to ?: \Yii::$app->params['robotEmail']);
        //Oh: cc to multiple user
        if($command->cc) {
            $message->setCc($command->cc);
        }
        $message->setSubject($command->subject);
        $result = $message->send();
        if($result != 1){
            echo "...fail\n";
            $m = new SysSendMessageError();
            $m->type = SysSendMessageError::TYPE_EMAIL;
            $m->category = $command->view;
            $m->recipient = $command->to;
            $m->param1 = $command->subject;
            $m->param2 = "";

            if (!$m->save()) {
                throw new ServerErrorHttpException('Error.');
            }
            return false;
        } else {
            // echo "...success\n";
            return true;
        }
    }
}