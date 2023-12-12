<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use common\behaviors\CacheInvalidateBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "key_storage_item".
 *
 * @property integer $key
 * @property integer $value
 */
class KeyStorageItem extends ActiveRecord
{
    const FRONTEND_MAINTENANCE_MODE = "frontend_maintenance_mode";
    const DASHBOARD_MAINTENANCE_MODE = "dashboard_maintenance_mode";
    const BACKEND_MAINTENANCE_MODE = "backend_maintenance_mode";

    const APP_MAINTENANCE_MODE = "app_maintenance_mode";
    const APP_MAINTENANCE_MESSAGE = "app_maintenance_message";
    const APP_ANNOUNCEMENT_MODE = "app_announcement_mode";
    const APP_ANNOUNCEMENT_MESSAGE = "app_announcement_message";
    const APP_VERSION_ANDROID = "app_version_android";
    const APP_VERSION_IOS = "app_version_ios";
    //const APP_VERSION_ANDROID_MAJOR = "app_version_android_major";
    //const APP_VERSION_IOS_MAJOR = "app_version_ios_major";

    const APP_VERSION_ANDROID_DEPRECATE = "app_version_android_deprecate";
    const APP_VERSION_IOS_DEPRECATE = "app_version_ios_deprecate";

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%key_storage_item}}';
    }

    public function behaviors()
    {
        return [
            'timestamp' => TimestampBehavior::className(),
            'cacheInvalidate'=> [
                'class' => CacheInvalidateBehavior::className(),
                /*
                'cacheComponent' => "cache",
                'keys' => [
                    KeyStorageItem::APP_MAINTENANCE_MODE,
                    KeyStorageItem::APP_MAINTENANCE_MESSAGE,
                    KeyStorageItem::APP_ANNOUNCEMENT_MODE,
                    KeyStorageItem::APP_ANNOUNCEMENT_MESSAGE,
                    KeyStorageItem::APP_VERSION_ANDROID,
                    KeyStorageItem::APP_VERSION_IOS,
                    KeyStorageItem::APP_VERSION_ANDROID_DEPRECATE,
                    KeyStorageItem::APP_VERSION_IOS_DEPRECATE,
                ]
                */
            ]
            
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['key', 'value'], 'required'],
            [['key'], 'string', 'max' => 128],
            [['value', 'comment'], 'safe'],
            [['key'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'key' => Yii::t('common', 'Key'),
            'value' => Yii::t('common', 'Value'),
            'comment' => Yii::t('common', 'Comment'),
        ];
    }

    public function toObject() {
        $m = $this;
        $o = (object) [];
        $o->key = $m->key;
        $o->value = $m->value;
        //don't show desc for public users
        //$o->description = $m->description;

        return $o;
    }
    public static function toObjectArray($models) {
        $d = [];
        foreach ($models as $m) {
            $o = $m->toObject();
            $d[] = $o;
        }
        return $d;
    }
}
