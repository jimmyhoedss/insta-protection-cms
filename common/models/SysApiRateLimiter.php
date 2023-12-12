<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sys_api_rate_limiter".
 *
 * @property int $user_id
 * @property string $endpoint
 * @property int|null $allowance
 * @property int|null $allowance_updated_at
 */
class SysApiRateLimiter extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sys_api_rate_limiter';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'endpoint'], 'required'],
            [['user_id', 'allowance', 'allowance_updated_at'], 'integer'],
            [['endpoint'], 'string', 'max' => 128],
            [['user_id', 'endpoint'], 'unique', 'targetAttribute' => ['user_id', 'endpoint']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'User ID',
            'endpoint' => 'Endpoint',
            'allowance' => 'Allowance',
            'allowance_updated_at' => 'Allowance Updated At',
        ];
    }

    /**
     * {@inheritdoc}
     * @return \common\models\query\SysApiRateLimiterQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\SysApiRateLimiterQuery(get_called_class());
    }
}
