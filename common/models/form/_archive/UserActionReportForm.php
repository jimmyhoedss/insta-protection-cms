<?php
namespace common\models\form;

use common\models\UserActionHistory;
use yii\base\InvalidParamException;
use yii\base\Model;
use Yii;

class UserActionReportForm extends Model
{
    public $action;
    public $start_date;
    public $end_date;
    
    
    const VIEW_DEFAULT = "user-action";
    const VIEW_VISIT = "user-action-visit";
    const VIEW_AR_SCAN = "user-action-ar-scan";
    const VIEW_UPLOAD = "user-action-upload";
    const VIEW_QUEST_COMPLETED = "user-action-quest-completed";
    const VIEW_HIDDEN_FRUIT_FOUND = "user-action-hidden-fruit";
    const VIEW_HIDDEN_FRUIT_FOUND_RARE = "user-action-hidden-fruit-rare";
    const VIEW_REDEEM_REWARD = "user-action-redeem-reward";
    const VIEW_ALLOCATED_REWARD = "user-action-allocated-reward";


    public function rules()
    {
        return [
            [['action'], 'string'],
            [['start_date', 'end_date'], 'integer'],
            [['start_date', 'end_date'],'default', 'value' => NULL]
        ];
    }

    public function attributeLabels()
    {
        return [
            'action'=>'Action',
            'start_date'=>'Start Date',
            'end_date'=>'End Date'
        ];
    }

}