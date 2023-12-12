<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\base\Model;

class SysSocketNotification extends Model
{
    const NOTIFY_SCAN_QR_PLAN_POOL = "notify_scan_qr_plan_pool";
    const NOTIFY_DEVICE_ASSESSMENT = "notify_device_assessment";

    const RESULT_SUCCESS = "success";
    const RESULT_FAIL = "fail";
    
    public $type; //scan qr or device assessment
    public $user_id; //recipient
    public $result; //success or fail
    public $msg; //optional msg

    public function rules()
    {
        return [
            [['type', 'user_id', 'result'], 'required'],
            [['type'], 'in', 'range' => [self::NOTIFY_SCAN_QR_PLAN_POOL, self::NOTIFY_DEVICE_ASSESSMENT]], 
            //[['user_id'], 'integer'],
            [['msg'], 'string', 'max' => 1024],
        ];
    }

    public static function makeModel($type, $user_id, $result=self::RESULT_SUCCESS, $msg="") {
        $m = new SELF();
        $m->type = $type;
        $m->user_id = $user_id;
        $m->result = $result;
        return $m;
    }

    public function send() {
        $m = $this;
        if ($this->validate()) {
            $o = (object) [];
            $o->type = $m->type;
            $o->user_id = $m->user_id;
            $o->result = $m->result;
            $o->msg = $m->msg;

            $s = json_encode($o);
            Yii::$app->rabbitMq->delay(0)->push($o);
            
            //loynote:: sqs not suitable
            //Yii::$app->awsSqs->delay(0)->push($o);
            return true;
        } else {
            $msg = "Fail to send socket. type:" . $m->type . " user_id:" . $m->user_id . " result:" . $m->result . " msg:" . $m->msg;
            Yii::warning($msg, 'SysSocketNotification');
            return false;
        }
    }


    




}