<?php

namespace common\models\fcm;

use Yii;
use yii\web\ServerErrorHttpException;
use common\jobs\FcmQueueJob;
use common\models\InstapPlanPool;
use common\models\User;
use common\models\InstapPlan;
use common\models\SysRegion;
use common\models\fcm\SysFcmMessage;
use common\models\fcm\PushNotification;

class FcmStockRequest extends \yii\base\Model
{
    public $notification;

    public function __construct($dealer_user, $plan_id, $amount, $company_downline){
        Yii::$app->language = SysRegion::mapCountryToNativeLanguage($dealer_user->user->region_id);
        $this->notification = new PushNotification();
        $this->notification->customSetAttributes(PushNotification::TYPE_INBOX, ["title"=>Yii::t("common","New Stock Request"), "summary"=>$this->getSummary($company_downline, $plan_id, $amount), "body"=> Yii::t("common","One of your downline company has requested stock")]);
        $this->notification->setRecipient(PushNotification::RECIPIENT_TYPE_DEVICE , $dealer_user->user_id);
    }

    public function send(){
        if($this->notification->saveInbox()){
            $this->notification->send();
        } else {
            Yii::warning($this->notification->getMessage(), 'api');
        }
    }
    
    private function getSummary($company_downline, $plan_id, $amount){
        $plan = InstapPlan::find()->andWhere(['id'=>$plan_id])->one();
        return $company_downline->business_name . " has requested for ". $amount . " of " . $plan->name;
    }
}