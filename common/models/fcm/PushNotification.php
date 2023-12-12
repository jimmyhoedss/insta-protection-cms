<?php

namespace common\models\fcm;

use Yii;
use common\models\query\SysFcmMessageQuery;
use common\commands\SendFcmMessageCommand;
use common\models\UserActionHistory;
use common\models\UserFcmInbox;
use common\models\User;
use common\jobs\FcmQueueJob;
use common\jobs\FcmActionQueueJob;
use yii\base\Model;
use yii\db\ActiveRecord;
use yii\web\ServerErrorHttpException;
use yii\web\NotFoundHttpException;
use yii\helpers\ArrayHelper;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
/*
    $notification = new PushNotification();
    $notification->customSetAttributes(PushNotification::TYPE_INBOX, ["title"=>"Stay tuned for a new event in June!", "summary"=>"Hello world", "body"=>"More details will be released in two weeks time! Look out for it!"]);
    $notification->setRecipient(PushNotification::RECIPIENT_TYPE_DEVICE , 314);
    // $notification->setRecipient(PushNotification::RECIPIENT_TYPE_TOPIC , PushNotification::BROADCAST_ID);
    if($notification->saveInbox() && $notification->saveAuditLog()){
        $notification->send();
    } else {
        $str = $this->getSerialisedValidationError($notification);
        throw new CustomHttpException($str, CustomHttpException::UNPROCESSABLE_ENTITY);
        // deal with errors
    }
*/
class PushNotification extends Model
{
    const BROADCAST_ID = "/topics/system";
    const MAX_NUMBER_PER_BATCH = 30000;
    
    const TYPE_INBOX = "type_inbox";
    const TYPE_INBOX_BANNER = "type_inbox_banner";
    const TYPE_INBOX_HYPERLINK = "type_inbox_hyperlink";
    const TYPE_INBOX_BANNER_HYPERLINK = "type_inbox_banner_hyperlink";

    const TYPE_ACTION_ALERT = "type_action_alert";
    const TYPE_ACTION_LOGOUT_SILENT = "type_action_logout_silent";
    const TYPE_ACTION_LOGOUT_ALERT = "type_action_logout_alert";
    const TYPE_ACTION_DAILY_RESYNC = "type_action_daily_resync";
    const TYPE_ACTION_QR_SCAN_FEEDBACK = "type_action_qr_scan_feedback";

    const RECIPIENT_TYPE_DEVICE = "recipient_type_device";
    const RECIPIENT_TYPE_DEVICE_GROUP = "recipient_type_device_group";
    const RECIPIENT_TYPE_TOPIC = "recipient_type_topic";

    public $transaction;
    
    public $type;
    public $recipient;
    public $recipient_type;
    public $title;
    public $summary;
    public $body;
    public $hyperlink_url;
    public $hyperlink_text;
    public $banner_url;

    public $hasSetRecipient = false;

    public $fcm_token;
    public $notification_type;
    const NOTIFICATION_TYPE_INBOX = "0"; 
    const NOTIFICATION_TYPE_ACTION = "1"; 
    const NOTIFICATION_TYPES = Array(
        SELF::TYPE_INBOX => SELF::NOTIFICATION_TYPE_INBOX,
        SELF::TYPE_INBOX_BANNER => SELF::NOTIFICATION_TYPE_INBOX,
        SELF::TYPE_INBOX_HYPERLINK => SELF::NOTIFICATION_TYPE_INBOX,
        SELF::TYPE_INBOX_BANNER_HYPERLINK => SELF::NOTIFICATION_TYPE_INBOX,
        SELF::TYPE_ACTION_ALERT => SELF::NOTIFICATION_TYPE_ACTION,
        SELF::TYPE_ACTION_LOGOUT_ALERT => SELF::NOTIFICATION_TYPE_ACTION,
        SELF::TYPE_ACTION_LOGOUT_SILENT => SELF::NOTIFICATION_TYPE_ACTION,
        SELF::TYPE_ACTION_DAILY_RESYNC => SELF::NOTIFICATION_TYPE_ACTION,
        SELF::TYPE_ACTION_QR_SCAN_FEEDBACK => SELF::NOTIFICATION_TYPE_ACTION
    );

    public function rules()
    {
        return [
            [['type', 'recipient', 'recipient_type', 'hasSetRecipient'], 'required'],
            [['title', 'body'], 'required', 'on'=>SELF::allTypeInboxes()],
            [['title'], 'required', 'on'=>[SELF::TYPE_ACTION_ALERT, SELF::TYPE_ACTION_LOGOUT_ALERT]],

            [['banner_url'], 'required', 'on'=>SELF::TYPE_INBOX_BANNER],
            [['hyperlink_url', 'hyperlink_text'], 'required', 'on'=>SELF::TYPE_INBOX_HYPERLINK],
            [['hyperlink_url', 'hyperlink_text', 'banner_url'], 'required', 'on'=>SELF::TYPE_INBOX_BANNER_HYPERLINK],

            ['hasSetRecipient', 'boolean'],
            ['hasSetRecipient', 'compare', 'compareValue'=>true, 'message'=>"Please set your recipient before sending message!"],
            ['title', 'string', 'max'=>64],
            [['summary', 'hyperlink_text'], 'string', 'max'=>128],
            ['body', 'string', 'max'=>255],
            [['hyperlink_url', 'banner_url'], 'string', 'max'=>1024, 'message'=>'Please shorten your URL for {attribute}'],
            
            [['type'], 'in', 'range' => ArrayHelper::merge(SELF::allTypeInboxes(), SELF::allTypeActions())],
            [['recipient_type'], 'in', 'range' => [SELF::RECIPIENT_TYPE_DEVICE, SELF::RECIPIENT_TYPE_DEVICE_GROUP, SELF::RECIPIENT_TYPE_TOPIC]],
            //TODO:: rules for $recipient??
        ];
    }

    private static function allTypeInboxes() {
        return [
            SELF::TYPE_INBOX,
            SELF::TYPE_INBOX_BANNER,
            SELF::TYPE_INBOX_HYPERLINK,
            SELF::TYPE_INBOX_BANNER_HYPERLINK
        ];
    }

    private static function allTypeActions() {
        return [
            SELF::TYPE_ACTION_ALERT,
            SELF::TYPE_ACTION_LOGOUT_ALERT,
            SELF::TYPE_ACTION_LOGOUT_SILENT,
            SELF::TYPE_ACTION_DAILY_RESYNC,
            SELF::TYPE_ACTION_QR_SCAN_FEEDBACK
        ];
    }

    public function customSetAttributes($type, $data){
        if(is_array($data)){
            $this->transaction = Yii::$app->db->beginTransaction();
            $this->scenario = $type;
            $this->notification_type = SELF::NOTIFICATION_TYPES[$type];
            $this->type = $type;
            $this->title = isset($data["title"]) ? $data["title"] : null;
            $this->summary = isset($data["summary"]) ? $data["summary"] : null;
            $this->body = isset($data["body"]) ? $data["body"] : null;
            if($type == SELF::TYPE_INBOX_BANNER){
                $this->banner_url = isset($data["banner_url"]) ? $data["banner_url"] : null;
            } else if($type == SELF::TYPE_INBOX_HYPERLINK){
                $this->hyperlink_url = isset($data["hyperlink_url"]) ? $data["hyperlink_url"] : null;
                $this->hyperlink_text = isset($data["hyperlink_text"]) ? $data["hyperlink_text"] : null;
            } else if($type == SELF::TYPE_INBOX_BANNER_HYPERLINK){
                $this->banner_url = isset($data["banner_url"]) ? $data["banner_url"] : null;
                $this->hyperlink_url = isset($data["hyperlink_url"]) ? $data["hyperlink_url"] : null;
                $this->hyperlink_text = isset($data["hyperlink_text"]) ? $data["hyperlink_text"] : null;
            }
            return true;
        }
        return false;
    }

    public function setRecipient($recipient_type, $recipient){
        $this->recipient_type = $recipient_type;
        $this->recipient = $recipient;
        $this->hasSetRecipient = true; 
    }
    //TODO:: roll back function in case something failed

    public function saveInbox(){
        if($this->validate()){
            $model = UserFcmInbox::makeModel($this);
            if($this->recipient_type == SELF::RECIPIENT_TYPE_DEVICE){     
                $model->save();
            } else if($this->recipient_type == SELF::RECIPIENT_TYPE_TOPIC){     
                // $model->saveBroadcast();
                // save in send() function
                return true;
            } else if($this->recipient_type == SELF::RECIPIENT_TYPE_DEVICE_GROUP){
                //nothing to do now for this
                return true;
            }

            if ($model->hasErrors()) {
                $this->transaction->rollBack();
                $this->addErrors($model->getErrors());
                return false;
            } else {
                return true;
            }
        }
        return false;
    }

    public function saveAuditLog(){
        if($this->validate()){
            $model = SysFcmMessage::makeModel($this);
            if($model->save()){
                return true;
            } else {
                $this->transaction->rollBack();
                $this->addErrors($model->getErrors());
                return false;
            }
        }
        return false;
    }

    public function send(){
        $this->transaction->commit();
        if($this->recipient_type == SELF::RECIPIENT_TYPE_TOPIC){
            $this->fcm_token = $this->recipient; // boardcast_id was set in setRecipient() function
            $batch_id = time();

            $totalNumberOfUser = User::find()->orderBy(['id'=>SORT_DESC])->one()->id;
            $n = floor(($totalNumberOfUser/SELF::MAX_NUMBER_PER_BATCH));
            $numberOfBatches = $n * SELF::MAX_NUMBER_PER_BATCH == $totalNumberOfUser ? $n : $n+1;
            for ($i=1; $i <= $numberOfBatches; $i++) {
                $start = 1+(SELF::MAX_NUMBER_PER_BATCH*($i-1));
                $end = SELF::MAX_NUMBER_PER_BATCH*$i;
                if($i == $numberOfBatches){
                    $end = $totalNumberOfUser;
                }

                $path = str_replace("\\","/",Yii::getAlias('@backend/web/csv/'.$batch_id));
                $fileName = $i . '.csv';

                Yii::$app->queue->delay(0)->push(new \common\jobs\CreateInboxCsvQueueJob([
                    'notification' => $this,
                    'numberOfBatches' => $numberOfBatches,
                    'start' => $start,
                    'end' => $end,
                    'path' => $path,
                    'fileName' => $fileName,
                ]));
            }
        } else {
            if(User::isUserLoggedIn($this->recipient)){
                $this->fcm_token = User::findOne($this->recipient)->fcm_token;
                return Yii::$app->queue->delay(0)->push(new FcmQueueJob([
                    'notification' => $this
                ]));
            }
        }
    }

    public function attributeLabels()
    {
        return [
            'type' => 'Type',
            'recipient' => 'Recipient',
            'recipient_type' => 'Recipient Type',
            'title' => 'Title',
            'summary' => 'Summary',
            'body' => 'Body',
            'hyperlink_url' => 'Hyperlink URL',
            'hyperlink_text' => 'Hyperlink Text',
            'banner_url' => 'Banner URL',
        ];
    }

    public static function fcmTypes() {
        if(Yii::$app->user->can(User::ROLE_ADMINISTRATOR)){
            return [
                SELF::TYPE_INBOX => "Normal Notification",
                SELF::TYPE_INBOX_HYPERLINK => "Notification with hyperlink",
                SELF::TYPE_INBOX_BANNER => "Notification with banner",
                SELF::TYPE_INBOX_BANNER_HYPERLINK => "Notification with hyperlink and banner",
                SELF::TYPE_ACTION_ALERT => "System alert",
                SELF::TYPE_ACTION_LOGOUT_ALERT => "Force logout with alert",
                SELF::TYPE_ACTION_LOGOUT_SILENT => "Force logout without warning",
                SELF::TYPE_ACTION_DAILY_RESYNC => "Daily resync"
            ];
        } else {
            return [
                SELF::TYPE_INBOX => "Normal Notification",
                SELF::TYPE_INBOX_HYPERLINK => "Notification with hyperlink",
            ];
        }
    }

    public static function fcmTypeDescriptions(){
        if(Yii::$app->user->can(User::ROLE_ADMINISTRATOR)){
            return "[Normal Notification] - Save message into notification inbox & show push notification.<br>
                [Notification with hyperlink] - Save message into notification inbox <b>with hyperlink</b> & show push notification.<br>
                [Notification with banner] - Save message into notification inbox <b>with banner</b> & show push notification.<br>
                [Notification with hyperlink and banner] - Save message into notification inbox <b>with hyperlink and banner</b> & show push notification.<br>
                [System alert] - Show alert.<br>
                [Force logout with alert] - Force logout targeted user(s) with alert.<br>
                [Force logout] - Force logout targeted user(s).<br>
                [Daily resync] - Force a daily resync on targeted user(s).<br>";
        } else {
            return "[Normal Notification] - Save message into notification inbox & show push notification.<br>
                [Notification with hyperlink] - Save message into notification inbox <b>with hyperlink</b> & show push notification.<br>";

            /*return "[Normal Notification] - Save message into notification inbox & show push notification.<br>
                [Notification with hyperlink] - Save message into notification inbox <b>with hyperlink</b> & show push notification.<br>
                [Notification with banner] - Save message into notification inbox <b>with banner</b> & show push notification.<br>
                [Notification with hyperlink and banner] - Save message into notification inbox <b>with hyperlink and banner</b> & show push notification.<br>";*/
        }
    }

}