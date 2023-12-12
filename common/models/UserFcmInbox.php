<?php

namespace common\models;

use Yii;
use yii\web\ServerErrorHttpException;
use common\components\MyCustomActiveRecord;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use common\models\User;

/**
 * This is the model class for table "user_fcm_inbox".
 *
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string $subtitle
 * @property string $body
 * @property string $hyperlink_text
 * @property string $hyperlink_url
 * @property string $banner_url
                    $created_at
                    $status_read
                    $status_favorite
 */
class UserFcmInbox extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_fcm_inbox';
    }

    public function behaviors() {
        return [
            'timestamp'  => [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                ],
            ]
        ];
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'created_at'], 'integer'],
            [['title', 'subtitle', 'hyperlink_text'], 'string', 'max' => 255],
            [['body', 'hyperlink_url', 'banner_url'], 'string', 'max' => 1024],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'title' => 'Title',
            'subtitle' => 'Subtitle',
            'body' => 'Body',
            'hyperlink_text' => 'Hyperlink Text',
            'hyperlink_url' => 'Hyperlink Url',
            'banner_url' => 'Banner Url',
            'status_read' => 'Status Read',
            'status_favorite' => 'Status Favorite',
            'created_at' => 'Created At',
        ];
    }

    public function saveToInbox($model){

        $this->user_id = isset($model->to) ? (int)$model->to : null;
        $this->title = isset($model->title) ? $model->title : null;
        $this->subtitle = isset($model->subtitle) ? $model->subtitle : null;
        $this->body = isset($model->body) ? $model->body : null;
        $this->hyperlink_text = isset($model->hyperlink_text) ? $model->hyperlink_text : null;
        $this->hyperlink_url = isset($model->hyperlink_url) ? $model->hyperlink_url : null;
        $this->banner_url = isset($model->banner_url) ? $model->banner_url : null;

        if ($this->save()) {
            return true;
        }
        throw new ServerErrorHttpException('Error saving to inbox.');
    }

    public static function saveToInboxBroadcast($model){
        $transaction = Yii::$app->db->beginTransaction();
        try{
            //process data
            $title = isset($model->title) ? $model->title : null;
            $subtitle = isset($model->subtitle) ? $model->subtitle : null;
            $body = isset($model->body) ? $model->body : null;
            $hyperlink_text = isset($model->hyperlink_text) ? $model->hyperlink_text : null;
            $hyperlink_url = isset($model->hyperlink_url) ? $model->hyperlink_url : null;
            $banner_url = isset($model->banner_url) ? $model->banner_url : null;
            $timeNow = time();

            //prepare query
            $loopCount = 0;
            $data = array();
            $users = User::find()->all();
            foreach ($users as $user) {
                $loopCount++;
                $data[] = [$user->id, $title, $subtitle, $body, $hyperlink_text, $hyperlink_url, $banner_url, $timeNow];
                if($loopCount%20 == 0 || count($users) == $loopCount){                    
                    //execute query for every 20 rows or reached last row
                    $result = Yii::$app->db
                    ->createCommand()
                    ->batchInsert(SElF::tableName(), ['user_id', 'title','subtitle','body','hyperlink_text','hyperlink_url','banner_url','created_at'], $data)
                    ->execute();
                    //clear $data
                    $data = array();
                }
            }

            $transaction->commit();
            return $result;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    // for use at actionListMyInbox
    public function toObject() {
        $m = $this;
        $o = (object) [];
        $o->id = $m->id;
        $o->user_id = $m->user_id;
        $o->title = $m->title;
        $o->subtitle = $m->subtitle;
        $o->body = $m->body;
        $o->hyperlink_text = $m->hyperlink_text;
        $o->hyperlink_url = $m->hyperlink_url;
        $o->banner_url = $m->banner_url;
        $o->status_read = TRUE ? $m->status_read == 'true' : FALSE;
        $o->status_favorite = TRUE ? $m->status_favorite == 'true' : FALSE;
        $o->created_at = $m->created_at;
        return $o;
    }

        // $o->hyperlink_text = $m->hyperlink_text ? $m->hyperlink_text != null : "";
        // $o->hyperlink_url = $m->hyperlink_url ? $m->hyperlink_url != null : "";
        // $o->banner_url = $m->banner_url ? $m->banner_url != null : "";

    public function toObjectArray($models) {
        $d = [];
        foreach ($models as $m) {
            $o = $m->toObject();
            $d[] = $o;
        }
        return $d;
    }

    public function getNumberOfUnreadMessages(){
        return SELF::find()->Where(['user_id' => $this->user_id])->andWhere(['status_read'=> "false"])->count();
    }
    public static function makeModel($notification){
        $m = new SELF();
        $m->user_id = isset($notification->recipient) ? (int)$notification->recipient : null;
        $m->title = isset($notification->title) ? $notification->title : null;
        $m->subtitle = isset($notification->summary) ? $notification->summary : null;
        $m->body = isset($notification->body) ? $notification->body : null;
        $m->hyperlink_text = isset($notification->hyperlink_text) ? $notification->hyperlink_text : null;
        $m->hyperlink_url = isset($notification->hyperlink_url) ? $notification->hyperlink_url : null;
        $m->banner_url = isset($notification->banner_url) ? $notification->banner_url : null;
        $m->created_at = time();

        return $m;
    }
}