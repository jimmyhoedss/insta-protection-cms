<?php

namespace common\models\fcm;

use Yii;
use common\models\query\SysFcmMessageQuery;
use common\commands\SendFcmMessageCommand;
use common\models\UserActionHistory;
use common\models\UserFcmInbox;
use common\models\User;
use common\models\fcm\PushNotification;
use common\jobs\FcmQueueJob;
use common\jobs\FcmActionQueueJob;
use yii\db\ActiveRecord;
use yii\web\ServerErrorHttpException;
use yii\web\NotFoundHttpException;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

class SysFcmMessage extends \yii\db\ActiveRecord
{
    const TYPE_INDIVIDUAL = "individual";
    const TYPE_BROADCAST = "broadcast";

    const TYPES = Array(
        PushNotification::RECIPIENT_TYPE_DEVICE => self::TYPE_INDIVIDUAL,
        PushNotification::RECIPIENT_TYPE_DEVICE_GROUP => 'group',
        PushNotification::RECIPIENT_TYPE_TOPIC => self::TYPE_BROADCAST
    );

    public $user_id;

    public $type;
    public $recipient;
    public $recipient_type;
    
    public $title;
    public $summary;
    public $body;
    public $hyperlink_url;
    public $hyperlink_text;
    public $banner_url;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sys_fcm_message';
    }

    public function behaviors()
    {
        return [
            'timestamp'  => [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                ],
            ],
            "blame" => [
                'class' => BlameableBehavior::className(),
                'updatedByAttribute' => false,
            ],            
        ];
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'subtitle', 'hyperlink_text', 'action_type', 'type'], 'string', 'max' => 128],
            [['action', 'body'], 'string', 'max' => 255],
            [['fcm_token', 'hyperlink_url', 'banner_url'], 'string', 'max' => 1024],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'to' => 'To (User ID)',
            'title' => 'Title',
            'subtitle' => 'Subtitle',
            'body' => 'Body',
            'hyperlink_text' => 'Hyperlink Text',
            'hyperlink_url' => 'Hyperlink Url',
            'banner_url' => 'Banner Url',
            'action' => 'Message Type',
            'fcm_token' => 'Fcm Token',
            'created_at' => 'Created At',
            'created_by' => 'Created By (User ID)',
        ];
    }

    /**
     * {@inheritdoc}
     * @return SysFcmMessageQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new SysFcmMessageQuery(get_called_class());
    }

    public function getName(){
        return (new \ReflectionClass($this))->getShortName();
    }

    public static function makeModel($notification){
        $m = new SELF();
        $m->type = SELF::TYPES[$notification->recipient_type];
        $m->to = $notification->recipient;
        $m->action = $notification->type;
        $m->fcm_token = $notification->recipient_type == PushNotification::RECIPIENT_TYPE_TOPIC? PushNotification::BROADCAST_ID : User::findOne($notification->recipient)->fcm_token;
        $m->title = $notification->title;
        $m->subtitle = $notification->summary;
        $m->body = $notification->body;
        $m->hyperlink_text = $notification->hyperlink_text;
        $m->hyperlink_url = $notification->hyperlink_url;
        $m->banner_url = $notification->banner_url;

        return $m;
    }

    //LOYNOTE: notify admin of sys msg
    /*public static function sendMessageToAdmin($className, $msg) {
        $user = User::findOne(1);
        if ($user != null) {
            $fcm_token = $user->fcm_token;
            if ($fcm_token != null) {
                $model = new SELF();
                $model->type = SELF::TYPE_INDIVIDUAL;
                $model->to = $user->id;
                $model->title = 'System Alert'; //notification title
                $model->subtitle = 'Error occurred in ' . $className;
                $model->body = $msg . "\n\nError occurred on: " . date("H:i:s d-m-Y ");
                $model->action = SELF::ACTION_NOTIFICATION;
                $model->fcm_token = $fcm_token;
                Yii::$app->queue->delay(0)->push(new FcmQueueJob([
                    'model' => $model
                ]));
                return true;
            }
        }
        return false;
    }*/

}