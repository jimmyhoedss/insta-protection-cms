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

class FcmDealerDeleteStaff extends \yii\base\Model
{
    public $notification;

    public function __construct($dealer_user, $dealer_user_history, $notify_to){
        Yii::$app->language = SysRegion::mapCountryToNativeLanguage($dealer_user_history->user->region_id);
        
        $title = ($notify_to == User::ROLE_DEALER_MANAGER) ? Yii::t("common","Staff Removed") : Yii::t("common","You Have Been Removed From Company");
        $this->notification = new PushNotification();
        $this->notification->customSetAttributes(PushNotification::TYPE_INBOX, ["title"=> $title, "summary"=>$this->getSummary($dealer_user_history), "body"=>Yii::t("common","One of the staff was removed from company")]);
        $this->notification->setRecipient(PushNotification::RECIPIENT_TYPE_DEVICE , $dealer_user->user_id);
    }

    public function send(){
        if($this->notification->saveInbox()){
            $this->notification->send();
        } else {
            Yii::warning($this->notification->getMessage(), 'api');
        }
    }

    private function getSummary($dealer_user_history){
        return $dealer_user_history->user->userProfile->first_name." ".$dealer_user_history->user->userProfile->last_name." (". $dealer_user_history->user->mobile_number_full .") ". " was removed from ".$dealer_user_history->dealer->business_name;
    }
}