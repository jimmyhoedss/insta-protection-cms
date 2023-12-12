<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use common\components\MyCustomActiveRecord;
use common\behaviors\MyAuditTrailBehavior;

/**
 * This is the model class for table "sys_feedback".
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $subject
 * @property string $message
 * @property int $created_at
 */
class SysFeedback extends MyCustomActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sys_feedback';
    }

    //$this->detachBehavior("blame");

    public function behaviors() {
        return [
            "auditTrail" =>
                [
                    'class' => MyAuditTrailBehavior::className(),
                ],
            'timestamp'  => [
                    'class' => TimestampBehavior::className(),
                    'attributes' => [
                        ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                    ],
                ],
        ];
        //return parent::behaviors();
        /*
        return ArrayHelper::merge(
            parent::behaviors(),
            [
                'timestamp'  => [
                    'class' => TimestampBehavior::className(),
                    'attributes' => [
                        ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                    ],
                ],
            ]
        );
        */
            
    }


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'email', 'message'], 'required'],
            [['subject', 'message', 'status', 'notes'], 'string'],
            [['created_at'], 'integer'],
            [['name', 'email'], 'string', 'max' => 256],
            [['notes'], 'string', 'max' => 2048],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'email' => 'Email',
            'subject' => 'Subject',
            'message' => 'Message',
            'created_at' => 'Created At',
        ];
    }

    public static function subjects()
    {
        return [
            "general" => Yii::t('common', "General"),
            "technical" => Yii::t('common', "Technical"),
        ];
    }
}
