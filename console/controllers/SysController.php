<?php
namespace console\controllers;

use Yii;
use yii\console\Controller;
use yii\console\Exception;
use yii\helpers\ArrayHelper;

use linslin\yii2\curl;

use common\commands\SendFcmCommand;
use common\commands\SendEmailCommand;
use common\commands\AddToTimelineCommand;

use common\components\Utility;
use common\components\Settings;

use common\models\User;
use common\models\UserCase;
use common\models\SysSettings;
use common\models\InstapPlanPool;

use common\models\fcm\SysFcmMessage;
use common\models\fcm\FcmPlanStatusChanged;
use common\models\fcm\FcmCaseStatusChanged;

use cheatsheet\Time;

/*
use Amp\ByteStream\Message;
use Amp\Process\Process;
use Amp\Loop;
*/


class SysController extends Controller
{
    const API_ID = "34";
    const API_KEY = "m5aHjDJG2OFCPZeet5ASmF1PWgwrkuy7";

    public $connection;

    public function actionIndex() {
        echo "\n\nSys cron service runnning\n";
    }

    private function addToTimeline() {
        $host= gethostname();
        $ip = gethostbyname($host);
        Yii::$app->commandBus->handle(new AddToTimelineCommand([
            'category' => 'sys',
            'event' => 'cronjob',
            'data' => [
                'host' => $host,
                'ip' => $ip,
                'created_at' => strtotime('now')
            ]
        ]));
    }

    public function actionCheckAlias() {
        echo "\n";
        echo Yii::getAlias('@apiUrl');
        echo "\n";
        echo Yii::getAlias('@frontendUrl');
        echo "\n";
        echo Yii::getAlias('@backendUrl');
        echo "\n";
        echo Yii::getAlias('@storageUrl');
        echo "\n";  

/*        $resetLink = Yii::$app->urlManagerFrontend->createAbsoluteUrl(['/user/password-reset', 'token' => "123"]);
        echo $resetLink;
        echo "\n";          
*/
    }

    public function emptyQcdTable() {
        $sql = "TRUNCATE `qcd_device_capacity`;
                TRUNCATE `qcd_device_color`;
                TRUNCATE `qcd_device_maker`;
                TRUNCATE `qcd_device_model`;
                TRUNCATE `qcd_policy_plan`;
                TRUNCATE `qcd_policy_plan_band`;
                TRUNCATE `qcd_repair_centre`;";

        $this->connection->createCommand($sql)->execute();
        echo "empty Qcd table ok\n";
           
    }

    public function actionSyncQcd() {
        $this->connection = Yii::$app->getDb();
        $this->emptyQcdTable();
        //get all device brands
        $this->syncDeviceMaker();

        //get all device model and repair center from brand
        $res = $this->connection->createCommand('SELECT * FROM qcd_device_maker')->queryAll();
        $dmi = ArrayHelper::getColumn($res, 'device_maker_id');
        $this->syncQcdDeviceMakerData($dmi);  

        //get repair centre of device brand
        $this->syncQcdRepairCentre($dmi);

        
        $res = $this->connection->createCommand('SELECT * FROM qcd_device_model')->queryAll();
        $device_model_id_arr = ArrayHelper::getColumn($res, 'device_model_id');

        //get all device model colour & capacity
        $this->syncQcdDeviceModelColourCapacity($device_model_id_arr);


        // print_r($device_model_id);

        //get all policy plan
        $this->syncQcdPolicyPlan();
    }

    public function syncDeviceMaker() {
        //$curl = new curl\Curl();
        $url = env('QCD_GET_DEVICE_MAKERS');
        $res = $this->callQcdGet($url);
        $json = json_decode($res, true);
        //print_r($json);

        if ($json['status'] === "OK") {
            $data = $json["device_makers"];
            $sql = "INSERT INTO `qcd_device_maker` (`id`, `device_maker_id`, `device_maker`) VALUES ";
            foreach($data as $key => $val){
                $sql .= "(NULL, '" .$val['device_maker_id'] . "', '" .$val['device_maker'] . "'),";             
            }
            $sql = substr($sql, 0, -1);
            $sql .= ";";
            $this->connection->createCommand($sql)->execute();
            //echo $sql;
            echo "insert device maker ok\n";
        } else {
            echo "insert device maker fail\n";
        }
    }

    public function syncQcdDeviceMakerData($dmi) {
        $url = env('QCD_GET_DEVICE_MAKER_DATA');
        // $curl = new curl\Curl();
        // $device_model_id = array();
        // echo "\n\nSync from\n" . $curl->getUrl();
        
        $sql = "INSERT INTO `qcd_device_model` (`id`, `device_model_id`, `device_maker_id`, `device_type_id`, `device_model`) VALUES ";
            for($i = 0; $i < count($dmi); $i++) {
                $params = [
                'device_maker_id' => $dmi[$i]
                ];       
                $res = $this->callQcdPost($url, $params);
                $json = json_decode($res, true);
                $data = $json["device_models"];
                if ($json['status'] === "OK") {
                    foreach($data as $key => $val){
                    $sql .= "(NULL, '" .$val['device_model_id'] . "', '" .$json['device_maker_id'] . "', '" .$val['device_type_id'] . "', '" .$val['device_model'] . "'),";             
                    } 
                    // echo "insert device maker ok";
                } else {
                    echo "insert device model fail\n";
                    exit();
                } 
            } 
            $sql = substr($sql, 0, -1);
            $sql .= ";";
            // echo $sql;
            $this->connection->createCommand($sql)->execute();
            echo "insert device model ok\n";
    }

    public function syncQcdRepairCentre($dmi) {
        $url = env('QCD_GET_REPAIR_CENTRE');

        $sql = "INSERT INTO `qcd_repair_centre` (`id`, `repair_centre_id`, `repair_centre`, `country_code`, `state_name`, `city_name`, `state_code`, `address`, `opening_hours`, `email`, `telephone`, `is_service_hub`, `is_courier`, `device_maker_id`, `is_asp`, `state`) VALUES ";
        for($i = 0; $i < count($dmi); $i++) {
            $params = [
            'device_maker_id' => $dmi[$i]
            ];       
            $res = $this->callQcdPost($url, $params);
            $json = json_decode($res, true);

                if ($json['status'] === "OK") {
                    $data = $json["repair_centres"];
                    foreach($data as $key => $val){
                    $sql .= "(NULL, '" .$val['repair_centre_id'] . "', '" .$val['repair_centre'] . "', '" .$val['country_code'] . "', '" .$val['state_name'] . "', '" .$val['city_name'] . "', '" .$val['state_code'] . "', '" .$val['address'] . "', '" .$val['opening_hours'] . "', '" .$val['email'] . "', '" .$val['telephone'] . "', '" .$val['is_service_hub'] . "', '" .$val['is_courier'] . "', '" .$json['device_maker_id'] . "', '" .$val['is_asp'] . "', '" .$val['state'] . "'),";             
                    }
                   
                } else {
                    echo "insert repair centre fail \n";
                    exit();
                }

        } 
        $sql = substr($sql, 0, -1);
        $sql .= ";";
        // echo $sql;
        $this->connection->createCommand($sql)->execute();
        echo "insert repair centre ok \n"; 
    }

    public function syncQcdDeviceModelColourCapacity($device_model_id) {

        $url = env('QCD_GET_DEVICE_MODEL_DATA');
        
        $sql = "INSERT INTO `qcd_device_color` (`id`, `device_color_id`, `device_color`, `device_model_id`) VALUES";
        $sql2 = "INSERT INTO `qcd_device_capacity` (`id`, `device_id`, `device_capacity_id`, `device_capacity`, `device_model_id`) VALUES";
            for($i = 0; $i < count($device_model_id); $i++) {
                $params = [ 'device_model_id' => $device_model_id[$i] ];       
                $res = $this->callQcdPost($url, $params);
                $json = json_decode($res, true);
                $data = $json["device_colors"];
                $data2 = $json["device_capacities"];
                // print_r($data);cls
                if ($json['status'] === "OK") {
                    foreach($data as $key => $val)  {
                        $sql .= "(NULL, '" .$val['device_color_id'] . "', '" .$val['device_color'] . "', '" .$json['device_model_id'] . "'),";                      
                    } 

                    foreach($data2 as $key => $val)  {
                        $sql2 .= "(NULL, '" .$val['device_id'] . "', '" .$val['device_capacity_id'] . "', '" .$val['device_capacity'] . "', '" .$json['device_model_id'] . "'),";                      
                    } 
                    // echo "insert device maker ok";
                } else {
                    echo "insert device model color capacity fail\n";
                    exit();
                } 
            } 
            
            $sql = substr($sql, 0, -1);
            $sql .= ";";

            $sql2 = substr($sql2, 0, -1);
            $sql2 .= ";";

            // echo $sql;
            $this->connection->createCommand($sql)->execute();
            $this->connection->createCommand($sql2)->execute();

            echo "insert device model color capacity ok\n";
    }

    public function syncQcdPolicyPlan() {
        $url = "https://claims-staging.ix-sun.com/api/get_policy_plans" ;

        $sql = "INSERT INTO `qcd_policy_plan` (`id`, `policy_plan_id`, `policy_plan`) VALUES";
        $sql2 = "INSERT INTO `qcd_policy_plan_band` (`id`, `policy_plan_id`, `policy_plan_band_id`, `policy_plan_band`, `excess_repair`, `excess_replacement`) VALUES";

            $i = 0;      
            $res = $this->callQcdGet($url);
            $json = json_decode($res, true);
            $data = $json["policy_plans"];
            $data2 = $data[$i]["policy_plan_bands"];
            
            // print_r($data2);
            // exit();

            // print_r($data);
            if ($json['status'] === "OK") {
                foreach($data as $key => $val) {
                    $sql .= "(NULL, '" .$val['policy_plan_id'] . "', '" .$val['policy_plan'] . "'),";   
                        $i++;
                        foreach($data2 as $key2 => $val2) {
                            $sql2 .= "(NULL, '" .$val['policy_plan_id'] . "', '" .$val2['policy_plan_band_id'] . "', '" .$val2['policy_plan_band'] . "', '" .$val2['excess_repair'] . "', '" .$val2['excess_replacement'] . "'),";          
                        }            
                }
                 
                $sql = substr($sql, 0, -1);
                $sql .= ";";
                $sql2 = substr($sql, 0, -1);
                $sql2 .= ";";
                $this->connection->createCommand($sql)->execute();
                $this->connection->createCommand($sql2)->execute();
                echo "insert policy plan band ok\n";

            } else {
                echo "insert policy plan band fail\n";
                exit();
            } 
             
    }

    public function actionSyncQcdClaimDetails() {

    }

    public function callQcdGet($url) {
        $curl = new curl\Curl();
        $response = $curl->setHeaders(['ixsun-api-id' => self::API_ID , 'ixsun-api-key' => self::API_KEY])->get($url);
        return $response;
    }

    public function callQcdPost($url, $params) {
        $curl = new curl\Curl();
        $response = $curl->setHeaders(['ixsun-api-id' => self::API_ID ,'ixsun-api-key' => self::API_KEY,])->setRequestBody(json_encode($params))->post($url);
        return $response;
    }

    public function actionCheckForExpiredPlanPool(){
        try {
            InstapPlanPool::updateAll(['plan_status' => InstapPlanPool::STATUS_EXPIRED], ['and', ['<=', 'coverage_end_at', (strtotime('today midnight')+1)], ['like', 'plan_status', InstapPlanPool::STATUS_ACTIVE]]);
        } catch (Exception $e) {
            User::sendTelegramBotMessage("Failed to check for expired plan pool");
        }
    }
    
    public function actionSendReminderPlanRegistration(){
        // remind user about plan pending registeration for 3 days
        try {
            $pendingRegPolicies = InstapPlanPool::find()->where(['plan_status'=> InstapPlanPool::STATUS_PENDING_REGISTRATION])->andWhere(['and', 'updated_at + '.(Time::SECONDS_IN_A_DAY * 3).' > ' . strtotime('now')])->all();
            if(!empty($pendingRegPolicies)){
                foreach ($pendingRegPolicies as $policy) {
                    $fcm = new FcmPlanStatusChanged($policy);
                    $fcm->send();
                }
            }
        } catch (Exception $e) {
            User::sendTelegramBotMessage("Failed to send reminder notification about plan prending registeration");
            User::sendTelegramBotMessage(json_encode($e));
        }
    }
    
    public function actionSendReminderPlanClarification(){
        // remind user about plan require clarification for 3 days
        try {
            $pendingClarifyPolicies = InstapPlanPool::find()->where(['plan_status'=> InstapPlanPool::STATUS_REQUIRE_CLARIFICATION])->andWhere(['and', 'updated_at + '.(Time::SECONDS_IN_A_DAY * 3).' > ' . strtotime('now')])->all();
            if(!empty($pendingClarifyPolicies)){
                foreach ($pendingClarifyPolicies as $policy) {
                    $fcm = new FcmPlanStatusChanged($policy);
                    $fcm->send();
                }
            }
        } catch (Exception $e) {
            User::sendTelegramBotMessage("Failed to send reminder notification about plan require clarification");
            User::sendTelegramBotMessage(json_encode($e));
        }
    }
    
    public function actionSendReminderClaimClarification(){
        // remind user about claim require clarification for 3 days
        try {
            $pendingClarifyCase = UserCase::find()->where(['current_case_status'  => UserCase::CASE_STATUS_CLAIM_REQUIRE_CLARIFICATION])->andWhere(['and', 'updated_at + '.(Time::SECONDS_IN_A_DAY * 3).' > ' . strtotime('now')])->all();
            if(!empty($pendingClarifyCase)){
                foreach ($pendingClarifyCase as $case) {
                    $fcm = new FcmCaseStatusChanged($case);
                    $fcm->send();
                }
            }
        } catch (Exception $e) {
            User::sendTelegramBotMessage("Failed to send reminder notification about claim require clarification");
            User::sendTelegramBotMessage(json_encode($e));
        }
    }

}
