<?php

use yii\db\Migration;
use common\models\form\RegistrationForm;
use common\models\User;
use common\models\DealerCompany;
use common\models\DealerUser;
use common\models\DealerUserHistory;




class m200401_093354_UAT_Table extends Migration
{


    public function safeUp()
    {

        $this->createUsers();
        $this->createRoles();
        // $this->createCompanys();
        // $this->createDealerUsers();

       

}

    public function safeDown()
    {
        // echo "m200401_093354_UAT_Table cannot be reverted.\n";

        // return false
        $connection = Yii::$app->db;
        $sql = "TRUNCATE `rbac_auth_assignment`;
                TRUNCATE `user`;
                TRUNCATE `user_profile`;
                TRUNCATE `user_fcm_inbox`;
                TRUNCATE `user_case`;
                TRUNCATE `user_case_action`;
                TRUNCATE `user_case_action_photo`;
                TRUNCATE `user_plan`;
                TRUNCATE `user_plan_action`;
                TRUNCATE `user_plan_action_photo`;
                TRUNCATE `user_plan_detail`;
                TRUNCATE `user_plan_detail_edit`;
                TRUNCATE `user_plan_detail_edit_history`;
                TRUNCATE `dealer_company`;
                TRUNCATE `dealer_company_dealer`;
                TRUNCATE `dealer_order`;
                TRUNCATE `dealer_user`;
                TRUNCATE `dealer_user_history`;
                TRUNCATE `dealer_order_ad_hoc`;
                TRUNCATE `dealer_order_inventory`;
                TRUNCATE `dealer_order_inventory_overview`;
                TRUNCATE `dealer_inventory_allocation_history`;
                TRUNCATE `instap_plan_pool`;
                TRUNCATE `instap_plan_dealer_company`;
                TRUNCATE `qcd_claim_detail`;
                TRUNCATE `qcd_claim_registration`;";


                // TRUNCATE `instap_plan`;
                // TRUNCATE `instap_plan_localization`;
        $connection->createCommand("SET foreign_key_checks = 0")->execute();
        $connection->createCommand($sql)->execute();
        $connection->createCommand("SET foreign_key_checks = 1")->execute();

    }

    public function createUsers() {
        $user_arr = [
            [1, "SG", "65", "97479576"],
            [2 ,"SG", "65", "93732061"],
            [3 ,"SG", "65", "91372701"],
            [4 ,"SG", "65", "85712472"],
            [5 ,"SG", "65", "11111111"],
            [6 ,"SG", "65", "22222222"],
            [7 ,"SG", "65", "33333333"],
            [8 ,"SG", "65", "10000000"],
            [9 ,"SG", "65", "20000000"],
            [10,"SG", "65", "30000000"],
            [11,"SG", "65", "40000000"],
            [12,"SG", "65", "50000000"],
            [13,"SG", "65", "60000000"],
            [14,"SG", "65", "00000001"],
            [15,"SG", "65", "00000002"],
        ];

        try{

            foreach($user_arr as $v){
                $form = new RegistrationForm();
                $form->region_id = $v[1];
                $form->mobile_calling_code = $v[2];
                $form->mobile_number = $v[3];
                $form->mobile_number_full = $v[2].$v[3];
                $form->register();
                
            }

        } catch (\Exception $e) {
            $success = false;
            Yii::warning($e->getMessage(), 'insert user test');
        }
    }


    public function createRoles() {
        $auth = Yii::$app->authManager;
        $role_arr = [
            [1, User::ROLE_ADMINISTRATOR],
            [2, User::ROLE_ADMINISTRATOR],
            [3, User::ROLE_ADMINISTRATOR],
            [4, User::ROLE_ADMINISTRATOR],
            [5, User::ROLE_IP_ADMINISTRATOR],
            [6, User::ROLE_IP_MANAGER],
            [7, User::ROLE_IP_ADMIN_ASSISTANT],
        ];
        foreach($role_arr as $r){
            $auth->assign($auth->getRole($r[1]), $r[0]);
        }
    }

   
}

