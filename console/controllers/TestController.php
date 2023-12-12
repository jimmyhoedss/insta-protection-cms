<?php

namespace console\controllers;

use Yii;
use yii\base\Module;
use yii\console\Controller;
use yii\helpers\Console;
use common\commands\SendSmsCommand;
use common\commands\SendEmailCommand;
use common\models\InstapPlanPool;
use common\models\InstapPlan;
use common\models\UserCase;
use common\models\UserCaseAction;
use common\models\UserCaseActionLog;
use common\models\UserPlanAction;
use common\models\UserPlanActionLog;
/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class TestController extends Controller
{
    // public function actionEmail($email) {
    //     Yii::$app->commandBus->handle(new SendEmailCommand([
    //         'subject' => 'loytest',
    //         'view' => 'loytest',
    //         'to' => $email,
    //         'params' => [],
    //     ]));
    // }

    // public function actionSms($mobileNumber) {
    //     Yii::$app->commandBus->handle(new SendSmsCommand([
    //         'mobileNumber' => $mobileNumber,
    //         'message' => 'loytest'
    //     ]));
    // }

    // public function actionSms2() {
    //     Yii::$app->commandBus->handle(new SendSmsCommand([
    //         'mobileNumber' => "+6597479576",
    //         'message' => 'loytest'
    //     ]));
    // }

    const username = "elastic";
    const password = "QmvNVhIiVFzNzUh7hx9lv6IK";
    const url_base = "https://myels.es.us-west1.gcp.cloud.es.io:9243/";

    public function actionIndex(){
        // $this->uploadOrders();
        // $this->uploadClaimActions();
        $this->uploadPlanActions();
    }

    public function uploadOrders(){
        $query = "
            SELECT 
                a.id AS dealer_order_id,
                a.dealer_company_id,
                b.region_id AS dealer_company_region_id,
                b.business_registration_number AS dealer_company_business_registration_number,
                b.business_name AS dealer_company_business_name,
                b.business_address AS dealer_company_business_address,
                b.business_zip_code AS dealer_company_business_zip_code,
                b.business_phone AS dealer_company_business_phone,
                b.business_email AS dealer_company_business_email,
                b.business_country AS dealer_company_business_country,
                b.business_contact_person AS dealer_company_business_contact_person,
                a.dealer_user_id,
                c.mobile_number_full AS dealer_user_mobile_number_full,
                c.email AS dealer_user_email,
                a.plan_pool_id,
                d.policy_number AS plan_pool_policy_number,
                d.plan_status AS plan_pool_plan_status,
                d.coverage_start_at AS plan_pool_coverage_start_at,
                d.coverage_end_at AS plan_pool_coverage_end_at,
                e.sku AS plan_sku,
                e.name AS plan_name,
                e.description AS plan_description,
                e.master_policy_number AS plan_master_policy_number,
                e.retail_price AS plan_retail_price,
                e.premium_price AS plan_premium_price,
                e.dealer_price AS plan_dealer_price,
                e.region_id AS plan_region_id,
                a.price,
                a.order_mode,
                FROM_UNIXTIME(d.created_at, '%Y-%m-%dT%TZ') AS created_at
            FROM
                instaprotection.dealer_order AS a
                    LEFT JOIN
                dealer_company AS b ON a.dealer_company_id = b.id
                    LEFT JOIN
                user AS c ON a.dealer_user_id = c.id
                    LEFT JOIN
                instap_plan_pool AS d ON a.plan_pool_id = d.id
                    LEFT JOIN
                instap_plan AS e ON d.plan_id = e.id
            ORDER BY e.name;
        ";
        $results = \Yii::$app->db->createCommand($query)->queryAll();
        // ->getRawSql();
        // print_r($results);exit();


        foreach ($results as $result) {
            //merge with _id
            // $tempArr = array("_id" => $result['plan_pool_id']);
            // $newArr = array_merge($result, $tempArr);
            $ch = curl_init();
            curl_setopt( $ch,CURLOPT_URL, 'http://127.0.0.1:9200/dealer_order/_doc/'. $result['dealer_order_id']);
            curl_setopt( $ch,CURLOPT_POST, true );
            curl_setopt( $ch,CURLOPT_HTTPHEADER, array (
                'Content-Type: application/json'
            ));
            curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $result ) );
            $res = curl_exec($ch );
            curl_close( $ch );
            $json = json_decode($res, true);
            print_r($json['_seq_no']);
            print_r("\n");
        }

    }
    public function uploadClaimActions(){
        $query = "
            SELECT 
                a.id AS user_case_action_id,
                a.action_status,
                b.case_type,
                b.occurred_at,
                b.location,
                b.cost_repair,
                c.mobile_number_full AS user_mobile_number_full,
                c.email AS user_email,
                b.plan_pool_id,
                d.policy_number AS plan_pool_policy_number,
                d.plan_status AS plan_pool_plan_status,
                d.coverage_start_at AS plan_pool_coverage_start_at,
                d.coverage_end_at AS plan_pool_coverage_end_at,
                e.sku AS plan_sku,
                e.name AS plan_name,
                e.description AS plan_description,
                e.master_policy_number AS plan_master_policy_number,
                e.retail_price AS plan_retail_price,
                e.premium_price AS plan_premium_price,
                e.dealer_price AS plan_dealer_price,
                e.region_id AS plan_region_id,
                FROM_UNIXTIME(a.created_at, '%Y-%m-%dT%TZ') AS created_at
            FROM
                instaprotection.user_case_action AS a
                    LEFT JOIN
                user_case AS b ON a.case_id = b.id
                    LEFT JOIN
                user AS c ON b.user_id = c.id
                    LEFT JOIN
                instap_plan_pool AS d ON b.plan_pool_id = d.id
                    LEFT JOIN
                instap_plan AS e ON d.plan_id = e.id
            ORDER BY e.name;
        ";
        $results = \Yii::$app->db->createCommand($query)
        ->queryAll();

        foreach ($results as $result) {
            $ch = curl_init();
            curl_setopt( $ch,CURLOPT_URL, 'http://127.0.0.1:9200/claim_actions/_doc/'.$result['user_case_action_id']);
            curl_setopt( $ch,CURLOPT_POST, true );
            curl_setopt( $ch,CURLOPT_HTTPHEADER, array (
                'Content-Type: application/json'
            ));
            curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $result ) );
            $res = curl_exec($ch );
            curl_close( $ch );
            $json = json_decode($res, true);
            print_r($json['_seq_no']);
            print_r("\n");
        }

    }
    public function uploadPlanActions(){
        $query = "
            SELECT 
                a.id AS plan_action_id,
                a.action_status,
                c.mobile_number_full AS user_mobile_number_full,
                c.email AS user_email,
                a.plan_pool_id,
                d.policy_number AS plan_pool_policy_number,
                d.plan_status AS plan_pool_plan_status,
                d.coverage_start_at AS plan_pool_coverage_start_at,
                d.coverage_end_at AS plan_pool_coverage_end_at,
                e.sku AS plan_sku,
                e.name AS plan_name,
                e.description AS plan_description,
                e.master_policy_number AS plan_master_policy_number,
                e.retail_price AS plan_retail_price,
                e.premium_price AS plan_premium_price,
                e.dealer_price AS plan_dealer_price,
                e.region_id AS plan_region_id,
                FROM_UNIXTIME(a.created_at, '%Y-%m-%dT%TZ') AS created_at
            FROM
                instaprotection.user_plan_action AS a
                    LEFT JOIN
                instap_plan_pool AS d ON a.plan_pool_id = d.id
                    LEFT JOIN
                user AS c ON d.user_id = c.id
                    LEFT JOIN
                instap_plan AS e ON d.plan_id = e.id
            ORDER BY e.name;
        ";
        $results = \Yii::$app->db->createCommand($query)->queryAll();
        // print_r($results);exit();
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_USERPWD, self::username . ":" . self::password);
            curl_setopt( $ch,CURLOPT_POST, true );
            curl_setopt( $ch,CURLOPT_HTTPHEADER, array (
                'Content-Type: application/json'
            ));
            curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
        foreach ($results as $result) {
            // $end_point = 'plan_actions/_doc/'.$result['plan_action_id'];
            // $this->elsPost($end_point);
            // $ch = curl_init();
            // curl_setopt($ch, CURLOPT_USERPWD, self::username . ":" . self::password);
            curl_setopt( $ch,CURLOPT_URL, self::url_base.'plan_actions/_doc/'.$result['plan_action_id']);
            curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $result ) );
            $res = curl_exec($ch );
            $json = json_decode($res, true);
            echo $json['_seq_no'];
            echo "\n";

            
            // print_r($json['_seq_no']);
            // print_r("\n");
        }

        curl_close( $ch );
        
    }

    public function actionPlanActionLog() {
        //find the earliest plan registration action and insert to db.
        $models = InstapPlanPool::find()->all();
        $count = 0;
        foreach ($models as $m) {
            # code...
            // $pa = UserPlanAction::find()->andWhere(['plan_pool_id' => $m->id,'action_status' => UserPlanAction::ACTION_REGISTRATION])->orderBy(['created_at'=>SORT_DESC])->limit(1)->one();
            $logs = UserPlanAction::find()->andWhere(['plan_pool_id' => $m->id])->all();
            // echo $pa;
            // echo "\n";
            foreach ($logs as $v) {
                # code...
                $upa = UserPlanActionLog::makeModel2($m, $v);
                if($upa->save()) {
                    $count ++;
                    echo "success";
                    echo "\n";
                }else {
                    $err = $upa->getErrors();
                    echo $err;
                    exit();
                }
            }
            

        }

        echo "Totoal record inserted :" . $count;

        // foreach ($results as $result) {
        //     $ch = curl_init(); 
        //     curl_setopt( $ch,CURLOPT_URL, 'http://127.0.0.1:9200/plan_action_logs/_doc');
        //     curl_setopt( $ch,CURLOPT_POST, true );
        //     curl_setopt( $ch,CURLOPT_HTTPHEADER, array (
        //         'Content-Type: application/json'
        //     ));
        //     curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        //     curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
        //     curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $result ) );
        //     $res = curl_exec($ch );
        //     curl_close( $ch );
        //     $json = json_decode($res, true);
        //     print_r($json['_seq_no']);
        //     print_r("\n");
        // }
    }

    public function actionClaimActionLog() {
        ///toDO: case status not able to save completely

        //find the earliest claim registration action and insert to db.
        $models = UserCase::find()->all();
        $count = 0;
        foreach ($models as $m) {
        // $pool = $m->planPool;
        // print_r($pool);exit();

            # code...
            #claim action ca
            $logs = UserCaseAction::find()->andWhere(['case_id' => $m->id])->all();
            // echo $pa;
            // echo "\n";
            foreach ($logs as $v) {
                # code...
                $uca = UserCaseActionLog::makeModel2($m, $v);
                if($uca->save()) {
                    $count ++;
                    echo "success";
                    echo "\n";
                }else {
                    $err = $uca->getErrors();
                    echo $err;
                    exit();
                }
            }
            
        }

        echo "insert successful total : " .$count;
        
    }

    public function actionEls() {
        $param = '
            {
                "query": {
                    "bool" : {
                      "must": [
                         {
                           "range": {
                              "created_at": {
                                  "gte": "2020-09-06T11:14:58Z",
                                  "lte": "2021-04-06T11:16:13Z"
                              }
                            }
                         },
                        {
                          "match": {
                            "plan_pool_plan_status": "active"
                          }
                        }
                      ]
                    }
                }
            }';

        $ch = curl_init();
        curl_setopt( $ch,CURLOPT_URL, 'http://127.0.0.1:9200/dealer_order/_search' );
        curl_setopt( $ch,CURLOPT_POST, true );
        curl_setopt( $ch,CURLOPT_HTTPHEADER, array (
            'Content-Type: application/json'
        ));
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch,CURLOPT_POSTFIELDS, $param);
        $res = curl_exec($ch);
        curl_close( $ch );
        // $json = iterator_to_array($res);
        $json = json_decode($res, true);
        print_r($json);
        print_r("\n");

    }

    public function elsPost($end_point, $query = null) {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERPWD, self::username . ":" . self::password);
        curl_setopt( $ch,CURLOPT_URL, self::url_path.$end_point );
        curl_setopt( $ch,CURLOPT_POST, true );
        curl_setopt( $ch,CURLOPT_HTTPHEADER, array (
            'Content-Type: application/json'
        ));
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
        $res = curl_exec($ch);
        curl_close( $ch );
        // $json = iterator_to_array($res);
        $json = json_decode($res, true);
        print_r($json);
        print_r("\n");
    }
}


