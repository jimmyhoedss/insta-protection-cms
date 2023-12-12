<?php

namespace common\commands;

use Yii;
use yii\base\BaseObject;
use yii\web\ServerErrorHttpException;
use trntv\bus\interfaces\SelfHandlingCommand;
use Aws\Sns\SnsClient;

class SendSmsCommand extends BaseObject implements SelfHandlingCommand
{
    public $mobileNumber;
    public $message;

    public function init()
    {
        //$this->title = "NParks Notification";
    }

    public function handle($command) 
    {   
        echo "Sending SMS " . $this->mobileNumber;
        $client = new SnsClient([
            'credentials' => [
                'key'    => ''.env('AWS_KEY'),
                'secret' => ''.env('AWS_SECRET'),
            ],
            'region' => ''.env('AWS_SNS_REGION'),
            //'region' => 'ap-southeast-1',
            'version' => 'latest',
            'http' => [ 'verify' => false ],
        ]);

        $args = array(
            "MessageAttributes" => [
                        'AWS.SNS.SMS.SenderID' => [
                            'DataType' => 'String',
                            'StringValue' => env('AWS_SNS_SENDER_ID'),
                        ],
                        'AWS.SNS.SMS.SMSType' => [
                            'DataType' => 'String',
                            'StringValue' => 'Transactional'
                        ]
                    ],
            "Message" => $this->message,
            "PhoneNumber" => $this->mobileNumber
        );

        try {
            $res = $client->publish($args);
            echo "...success\n";
        } catch (AwsException $e) {
            // output error message if fails
            echo "...fail\n";
            throw new ServerErrorHttpException($e->getMessage());
        } 
    }

}