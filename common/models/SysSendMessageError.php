<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "sys_send_message_error".
 *
 * @property int $id
 * @property string $type
 * @property string $category
 * @property string $recipient
 * @property string $param1
 * @property string $param2
 * @property int $created_at
 */
class SysSendMessageError extends \yii\db\ActiveRecord
{
    const TYPE_FCM = 'fcm';
    const TYPE_EMAIL = 'email';
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sys_send_message_error';
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
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type'], 'string'],
            [['created_at'], 'integer'],
            [['category', 'recipient', 'param1', 'param2'], 'string', 'max' => 1024],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'category' => 'Category',
            'recipient' => 'Recipient',
            'param1' => 'Param1',
            'param2' => 'Param2',
            'created_at' => 'Created At',
        ];
    }

    /**
     * {@inheritdoc}
     * @return SysSendMessageErrorQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new SysSendMessageErrorQuery(get_called_class());
    }
}
