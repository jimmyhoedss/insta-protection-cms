<?php
namespace common\models\form;

use common\models\User;
use common\models\UserProfile;
use common\models\UserActionHistory;
use common\models\NparksQuestBanner;
use common\models\NparksQuestQuiz;
use common\models\NparksHiddenLocation;


use yii\base\Exception;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use Yii;

class UserJourneyActionForm extends Model
{
    public $action;
    public $parameter;
    public $latitude;
    public $longitude;
    public $device_id;
    public $device_type;
    public $app_version = null;

    const STATION_01 = "station01";
    const STATION_02 = "station02";
    const STATION_03 = "station03";
    const STATION_04 = "station04";
    const STATION_05 = "station05";
    const STATION_06 = "station06";
    const STATION_07 = "station07";
    const STATION_08 = "station08";
    const STATION_09 = "station09";
    const STATION_10 = "station10";

    public function init(){
    }

    public function rules()
    {

        return [
            [['action', 'latitude', 'longitude'], 'required'],
            [['action'], 'in', 'range' => UserActionHistory::getAllAction()],
            /*
            [
                'parameter',
                'required',
                'when' => function($model) {
                    return $model->action != UserActionHistory::ACTION_VISIT;
                }, 
                'enableClientValidation' => false,
            ],
            */
            [['action','parameter', 'device_id', 'device_type', 'app_version'], 'string'],
            ['parameter', 'validateParameter', 'skipOnEmpty' => false],
            
            //['parameter', 'exist', 'targetClass' => NparksQuestQuiz::class, 'targetAttribute' => ['parameter' => 'quest_key'], 'when' => function($m) { return $m->action == UserActionHistory::ACTION_QUEST_COMPLETED; }],

            //['parameter', 'exist', 'targetClass' => NparksQuestBanner::class, 'targetAttribute' => ['parameter' => 'quest_key'],'when' => function($m) { return $m->action == UserActionHistory::ACTION_QUEST_COMPLETED; }]

        ];
    }

    public function attributeLabels() {
        return [
            'user_id' => 'User ID',
            'type' => 'Type',
            'action' => 'Action',
            'parameter' => 'Parameter',
            'credit' => 'Credit',
            'latlng' => 'Latlng',
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',            
            'device_id' => 'Device Id',
            'device_type' => 'Device Type',
            'app_version' => 'APP Version',
        ];
    }

    public function validateParameter($attribute, $params){

        if ($this->action != UserActionHistory::ACTION_VISIT) {
            if ($this->parameter == null || $this->parameter == "") {
                $this->addError($this->parameter,'Parameter cannot be blank.');
            } else if ($this->action == UserActionHistory::ACTION_AR_SCAN) {
                if (!in_array($this->parameter, $this->allStations())) {
                    $this->addError($this->parameter,'Parameter out of range.');
                } 
            } else if ($this->action == UserActionHistory::ACTION_QUEST_COMPLETED) {
                if (!in_array($this->parameter, NparksQuestQuiz::getAllQuestKey()) && !in_array($this->parameter, NparksQuestBanner::getAllQuestKey())) {
                    $this->addError($this->parameter,'Parameter out of range.');
                }             
            } else if ($this->action == UserActionHistory::ACTION_HIDDEN_FRUIT_FOUND || $this->action == UserActionHistory::ACTION_HIDDEN_FRUIT_FOUND_RARE) {
                if (!in_array($this->parameter, NparksHiddenLocation::getAllId())) {
                    $this->addError($this->parameter,'Parameter out of range.');
                }             
            }
        }  
    }   

    public static function allStations() {
        return [
            self::STATION_01,
            self::STATION_02,
            self::STATION_03,
            self::STATION_04,
            self::STATION_05,
            self::STATION_06,
            self::STATION_07,
            self::STATION_08,
            self::STATION_09,
            self::STATION_10,
        ];
    }

}