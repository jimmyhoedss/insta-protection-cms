<?php

namespace common\models;

use Yii;
use common\components\MyCustomActiveRecord;

class SysSesTrace extends MyCustomActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sys_ses_trace';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // [['log_time'], 'required'],
            // [['log_time'], 'integer'],
            [['ip_address', 'email_to', 'controller', 'application', 'action'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ip_address' => 'Ip Address',
            'email_to' => 'email receiver',
            'log_time' => 'Log Time',
        ];
    }

    public function getIpAddress() {

    }

    public static function makeModel($email){
        $m = new SELF();
        $m->ip_address = Yii::$app->getRequest()->getUserIP();
        $m->application = Yii::$app->id;
        $m->controller = Yii::$app->controller->id;
        $m->action = Yii::$app->controller->action->id;
        $m->email_to = $email;
        return $m;
    }


    public static function find()
    {
        return new \common\models\query\SysSesTraceQuery(get_called_class());
    }
}
