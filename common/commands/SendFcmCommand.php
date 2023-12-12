<?php

namespace common\commands;

use Yii;
use yii\base\BaseObject;
use yii\web\ServerErrorHttpException;
use trntv\bus\interfaces\SelfHandlingCommand;
use common\models\fcm\PushNotification;
use common\models\User;

class SendFcmCommand extends BaseObject implements SelfHandlingCommand
{
    public $headers;
    public $fields;

    public function init()
    {
        //$this->title = "NParks Notification";
    }

    public function handle($command) {
        echo "sending fcm\n";
        #Send Reponse To FireBase Server    
        $ch = curl_init();
        curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
        curl_setopt( $ch,CURLOPT_POST, true );
        curl_setopt( $ch,CURLOPT_HTTPHEADER, $this->headers );
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $this->fields ) );
        $result = curl_exec($ch );
        curl_close( $ch );
        $json = json_decode($result, true);

        if ($this->fields["data"]["recipient"] == PushNotification::BROADCAST_ID){
            echo "...success to topics\n";
            return true;     
        } else {  
            if ($json["success"] >= 1){
                echo "...success\n";
                return true;
            } else {
                // User::sendTelegramBotMessage(json_encode( $this->fields ));
                // User::sendTelegramBotMessage(json_encode( $this->headers ));
                // User::sendTelegramBotMessage($result."");
                echo "...fail\n";
                throw new ServerErrorHttpException('Error sending FCM.');
            }
        }

    }

}