<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sys_log".
 *
 * @property string $id
 * @property int $level
 * @property string $category
 * @property double $log_time
 * @property string $prefix
 * @property string $message
 */
class SysLog extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sys_log';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['level'], 'integer'],
            [['log_time'], 'number'],
            [['prefix', 'message'], 'string'],
            [['category'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'level' => Yii::t('common', 'Level'),
            'category' => Yii::t('common', 'Category'),
            'log_time' => Yii::t('common', 'Log Time'),
            'prefix' => Yii::t('common', 'Prefix'),
            'message' => Yii::t('common', 'Message'),
        ];
    }
}
