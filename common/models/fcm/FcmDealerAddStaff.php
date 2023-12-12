<?php

namespace common\models\fcm;

use Yii;
use yii\web\ServerErrorHttpException;
use common\jobs\FcmQueueJob;
use common\models\InstapPlanPool;
use common\models\User;
use common\models\InstapPlan;
use common\models\SysRegion;
use common\models\DealerCompany;
use common\models\fcm\SysFcmMessage;
use common\models\fcm\PushNotification;

class FcmDealerAddStaff extends \yii\base\Model
{
    public $notification;

    public function __construct($dealer_user, $dealerStaff, $notify_to){
        //localisation
        Yii::$app->language = SysRegion::mapCountryToNativeLanguage($dealerStaff->user->region_id);

        $title = ($notify_to == User::ROLE_DEALER_MANAGER) ? Yii::t("common","New Staff added") : Yii::t("common","You are added to Company");
        $this->notification = new PushNotification();
        $this->notification->customSetAttributes(PushNotification::TYPE_INBOX, ["title"=>$title , "summary"=>$this->getSummary($dealerStaff), "body"=> Yii::t("common","New staff was added to company")]);
        $this->notification->setRecipient(PushNotification::RECIPIENT_TYPE_DEVICE , $dealer_user->user_id);
    }


    public function send(){
        if($this->notification->saveInbox()){
            $this->notification->send();
        } else {
            Yii::warning($this->notification->getMessage(), 'api');
        }
    }

    private function getSummary($dealerStaff){
        return $dealerStaff->userProfile->first_name." ".$dealerStaff->userProfile->last_name." (". $dealerStaff->user->mobile_number_full .") ". " was added to ". $dealerStaff->dealer->business_name;
    }
}