<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "api_rate_limiter".
 *
 * @property int $user_id
 * @property string $action
 * @property int $allowance
 * @property int $allowance_update_at
 */
class ApiRateLimiter extends \yii\db\ActiveRecord
{
    //TODO:: create trigger
    //entry "trip/save-point"
    //created by trigger on user create

    public static function tableName()
    {
        return 'sys_api_rate_limiter';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'endpoint'], 'required'],
            [['user_id', 'allowance', 'allowance_updated_at'], 'integer'],
            [['endpoint'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => Yii::t('app', 'User ID'),
            'endpoint' => Yii::t('app', 'Endpoint'),
            'allowance' => Yii::t('app', 'Allowance'),
            'allowance_updated_at' => Yii::t('app', 'Allowance Updated At'),
        ];
    }

    public static function findEntry($user_id, $endpoint)
    {
        $model = static::find()->andWhere(['user_id' => $user_id, 'endpoint'=> $endpoint])->one();
        return $model;
    }
}
