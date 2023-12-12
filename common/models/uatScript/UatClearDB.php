<?php
namespace common\models\uatScript;

use Yii;
use yii\base\InvalidParamException;
use yii\base\Model;
use yii\web\JsExpression;
use common\components\MyCustomBadRequestException;
use common\components\MyLocalization;
use common\components\MyCustomActiveRecord;


use common\models\User;
use common\models\UserProfile;
use common\models\DealerCompany;
use common\models\DealerCompanyDealer;
use common\models\DealerUser;
use common\models\DealerUserHistory;
use common\models\DealerOrderInventoryOverview;
use common\models\DealerInventoryAllocationHistory;
use common\models\InstapPlan;
use common\models\InstapPromotion;
use common\models\InstapPlanLocalization;
use common\models\InstapPlanDealerCompany;
use common\models\InstapPromotionLocalization;
use common\models\form\RegistrationForm;
use common\models\form\UpdateProfileForm;


/**
 * Password reset form
 */
class UatClearDB {
    //array of plans available for company to sell, reference to instap_plan_dealer_company table
    // public $plan_id_arr= [];

    // public function rules()
    // {
    //     return [          
    //         [['plan_id_arr'], 'safe'],            
    //     ];
    // }
    
 
    // public function attributeLabels()
    // {
    //     return [
    //         'plan_id_arr'=>Yii::t('common', 'Plan')
    //     ];
    // }

    // public static function clearDB() {
    //     $success = false;
    //     $transaction = Yii::$app->db->beginTransaction();
    //     try {
    //         $delete = self::deleteData();
    //         if($delete) {
    //             self::insertDefaultUser();
    //         }
    //         $success = true;
    //     } catch (\Exception $e) {
    //          $success = false;
    //     }

    //     if($success) {
    //          $transaction->commit();
    //     } else {
    //         $transaction->rollBack();
    //     }
        
    // }

    public static function resetDb() {
        self::clearDb();
        $checkTable = self::checkAllTableEmpty();
        if($checkTable) {
            try {
                self::createUsers();
                self::createRoles();
                // self::createCompanys();
                // self::createDealerUsers();
                // self::createCompanyRelation();
                // self::createCompanyPlan();
                // self::createInventory();
                $d = true;
            } catch(\Exception $e) {
                // print_r($e);
                $d = false;
            }
        }

        return $d; 
    }


    public function clearDb() {
        // echo "m200401_093354_UAT_Table cannot be reverted.\n";

        // return false
        $connection = Yii::$app->db;
        $sql = "TRUNCATE `rbac_auth_assignment`;
                TRUNCATE `user`;
                TRUNCATE `user_profile`;
                TRUNCATE `user_fcm_inbox`;
                TRUNCATE `user_case`;
                TRUNCATE `user_case_action`;
                TRUNCATE `user_case_action_document`;
                TRUNCATE `user_case_repair_centre`;
                TRUNCATE `user_plan`;
                TRUNCATE `user_plan_action`;
                TRUNCATE `user_plan_action_document`;
                TRUNCATE `user_plan_detail`;
                TRUNCATE `user_plan_detail_edit`;
                TRUNCATE `user_plan_detail_edit_history`;
                TRUNCATE `dealer_order`;
                TRUNCATE `dealer_user`;
                TRUNCATE `dealer_user_history`;
                TRUNCATE `dealer_order_ad_hoc`;
                TRUNCATE `dealer_order_inventory`;
                TRUNCATE `dealer_order_inventory_overview`;
                TRUNCATE `dealer_inventory_allocation_history`;
                TRUNCATE `instap_plan_pool`;
                TRUNCATE `instap_report`;
                TRUNCATE `instap_promotion`;
                TRUNCATE `instap_promotion_localization`;
                TRUNCATE `instap_plan_dealer_company`;
                TRUNCATE `sys_user_token`;
                TRUNCATE `sys_audit_trail`;
                TRUNCATE `sys_file_storage_item`;
                TRUNCATE `sys_fcm_token_history`;
                TRUNCATE `sys_log`;
                TRUNCATE `sys_login_history`;
                TRUNCATE `sys_oauth_access_token`;
                TRUNCATE `sys_send_message_error`;
                TRUNCATE `timeline_event`;
                TRUNCATE `qcd_claim_detail`;
                TRUNCATE `qcd_claim_registration`;
                TRUNCATE `instap_plan`;
                TRUNCATE `instap_plan_localization`;
                ";


        $connection->createCommand("SET foreign_key_checks = 0")->execute();
        $connection->createCommand($sql)->execute();
        $connection->createCommand("SET foreign_key_checks = 1")->execute();

    }

    public function checkAllTableEmpty() {
        $tables = [`rbac_auth_assignment`,`user`,`user_profile`,`user_fcm_inbox`,`user_case`,`user_case_action`,`user_case_action_photo`,`user_plan`,`user_plan_action`,`user_plan_action_photo`,`user_plan_detail`,`user_plan_detail_edit`,`user_plan_detail_edit_history`,`dealer_company`,`dealer_company_dealer`,`dealer_order`,`dealer_user`,`dealer_user_history`,`dealer_order_ad_hoc`,`dealer_order_inventory`,`dealer_order_inventory_overview`,`dealer_inventory_allocation_history`,`instap_plan_pool`,`instap_plan_dealer_company`,`qcd_claim_detail`,`qcd_claim_registration`];

        $connection = Yii::$app->db;
        $tableNames = $connection->schema->getTableNames();

        $count = count($tables);
        $index = 0;
        foreach ($tables as  $v) {
            if (!isset($tableNames[$v])) {
                $index ++;
            }
        }
        if($count == $index) {
            return true;
        }

        return false;

    }


    
    public function createUsers() {
        $user_arr = [
            [1, "SG", "65", "97479576"],//super admin
            [2 ,"SG", "65", "93732061"],//super admin
            [3 ,"SG", "65", "91372701"],//super admin
            [4 ,"SG", "65", "85712472"],//super admin
            [5 ,"SG", "65", "98008536"],//ip super admin
            [6 ,"SG", "65", "96933393"],//ip super admin
        ];

        try{

            foreach($user_arr as $v){
                $form = new RegistrationForm();
                $form->region_id = $v[1];
                $form->mobile_calling_code = $v[2];
                $form->mobile_number = $v[3];
                $form->mobile_number_full = $v[2].$v[3];
                $form->register();

                // self::userProfile($v[0]);
            }

        } catch (\Exception $e) {
            $success = false;
            Yii::warning($e->getMessage(), 'insert user test');
        }
    }


    /*public function createUsers() {
        $user_arr = [
            [1, "SGIP-SP-CP01", "SG", "SG-MASTER-CP01", "BASIC+", "Vivo, Xiaomi", "https://protect.instaprotection.com/policy/sgip-sp-cp01", "https://s3-ap-southeast-1.amazonaws.com/storage.instaprotection", "media/plan/1/kQ6sSdUrYAbFrqdYpUncaDX2S4m-tWX8.jpg", 12, 33.90, 30, 32, ],//super admin
        ];

        try{

            foreach($user_arr as $v){
                $form = new RegistrationForm();
                $form->region_id = $v[1];
                $form->mobile_calling_code = $v[2];
                $form->mobile_number = $v[3];
                $form->mobile_number_full = $v[2].$v[3];
                $form->register();

                self::userProfile($v[0]);
            }

        } catch (\Exception $e) {
            $success = false;
            Yii::warning($e->getMessage(), 'insert user test');
        }
    }*/


    public function createRoles() {
        $auth = Yii::$app->authManager;
        $role_arr = [
            [1, User::ROLE_ADMINISTRATOR],
            [2, User::ROLE_ADMINISTRATOR],
            [3, User::ROLE_ADMINISTRATOR],
            [4, User::ROLE_ADMINISTRATOR],
            [5, User::ROLE_IP_SUPER_ADMINISTRATOR],
            [6, User::ROLE_IP_SUPER_ADMINISTRATOR],
            // [7, User::ROLE_IP_ADMIN_ASSISTANT],
            // [16, User::ROLE_ADMINISTRATOR],
        ];
        foreach($role_arr as $r){
            $auth->assign($auth->getRole($r[1]), $r[0]);
        }
    }

    public function createCompanys() {
        $company_arr = [   
            [1,'SG','BS100000','IOIO LAB PTE LTD','25 KALLANG AVE 10, #02-02','55555','+65 6291 6291','IOIOLAB@HOTMAIL.COM',NULL,NULL,'ad_hoc','none','','unknow','Yishun','Singapore','Mr.abc'],
            [2,'SG','BS1000001','InstaProtection Sdn Bhd','77 Robinson Road, #24-01','666666','0167123498','INSTAPROTECTION@gmail.com',NULL,NULL,'ad_hoc','none','','unknow','Yishun','Singapore','Mr.bean'],
            [3,'SG','BS222222','MOBILE COMPANY (ALONG) -0','180, #02-03 Kitchener Rd','678903','6512345678','MOBILECOMPANY@YAHOO.COM.SG',NULL,NULL,'stockpile','allocate_only','','unknow','Yishun','Singapore','Mr.hello'],
            [4,'SG','BS222223','MOBILE COMPANY (AKAU) -1','1 Joo Koon Circle (Fair Price Hub) #02-21','645768','6589067774','MOBILECOMPANY1@YAHOO.COM.SG',NULL,NULL,'stockpile','allocate_or_activate','FDSFSDF','unknow','Yishun','Singapore','Mr.lee'],
            [5,'SG','BS222224','MOBILE COMPANY (ALI) -2','750A Chai Chee Rd #01-01','81750','6571234678','MOBILECOMPANY_ALI@gmail.com',NULL,NULL,'stockpile','activate_only','DFD','unknow','Yishun','Singapore','Mr.leong'],
            [6,'SG','BS333331','MOBILE HOSEH (HOSEH)','Blk 355 Ang Mo Kio Ave 5, #01-48','647893','659883213','MOBILE_HOSEH@GMAIL.COM',NULL,NULL,'stockpile','allocate_only','FDSFDS','unknow','Yishun','Singapore','Mr.chan'],
            [7,'SG','BS333332','MOBILE HOSEH (HOYEH)','77 Robinson Road, #24-01','567666','658905363','MOBILEHOSEH@gmail.com',NULL,NULL,'stockpile','allocate_or_activate','fdsfsd','unknow','Yishun','Singapore','Mr.lim'],
            [8,'SG','BS000001','ONLY AD HOC COMPANY','1 Joo Koon Circle (Fair Price Hub) #02-21','4567899','6578905673','ONLY_AD_HOC@GMAIL.COM',NULL,NULL,'ad_hoc','none','DFDGFDG','unknow','Yishun','Singapore','Mr.koh']
        ];
        $success = false;
        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach($company_arr as $c) {
                $m = new DealerCompany();
                $m->region_id = $c[1];
                $m->business_registration_number = $c[2];
                $m->business_name = $c[3];
                $m->business_address = $c[4];
                $m->business_zip_code = $c[5];
                $m->business_phone = $c[6];
                $m->business_email = $c[7];
                $m->sp_inventory_order_mode = $c[10];
                $m->sp_inventory_allocation_mode = $c[11];
                $m->notes = $c[12];
                $m->business_city = $c[13];
                $m->business_state = $c[14];
                $m->business_country = $c[15];
                $m->business_contact_person = $c[16];

                $m->save();
            }
            $success = true;
        } catch (\Exception $e) {
            Yii::warning($e->getMessage(), 'insert company insert');
        }

        if($success) {
            $transaction->commit();
           return true;
        } else {
            $transaction->rollBack();
        }

        return false; 
    }

    public function createDealerUsers() {

        $success = false;
        $auth = Yii::$app->authManager;
        // [user_id, company_id, role]
        $dealer_user_arr = [
            [1 ,1, User::ROLE_DEALER_MANAGER],
            [2 ,1, User::ROLE_DEALER_MANAGER],
            [3 ,1, User::ROLE_DEALER_MANAGER],
            [4 ,1, User::ROLE_DEALER_MANAGER],
            [5 ,2, User::ROLE_DEALER_MANAGER],
            [6 ,2, User::ROLE_DEALER_MANAGER],
            [7 ,2, User::ROLE_DEALER_MANAGER],
            [16,2, User::ROLE_DEALER_MANAGER],
            [8 ,3, User::ROLE_DEALER_MANAGER],
            [9 ,4, User::ROLE_DEALER_MANAGER],
            [10 ,5, User::ROLE_DEALER_MANAGER],
            [11 ,6, User::ROLE_DEALER_MANAGER],
            [12 ,7, User::ROLE_DEALER_MANAGER],
            [13 ,8, User::ROLE_DEALER_MANAGER],
        ];

        $transaction =  Yii::$app->db->beginTransaction();
        try {
            foreach($dealer_user_arr as $v){
                $m = new DealerUser();
                $m->user_id = $v[0];
                $m->dealer_company_id = $v[1];
                $dh = DealerUserHistory::makeModel($v[0], $v[1], DealerUserHistory::ACTION_ADD_ROLE, $v[2]);
                $m->save();
                $dh->save();
                $auth->assign($auth->getRole($v[2]), $v[0]);
            }

            $success =true;

        } catch (\Exception $e) {
            Yii::warning($e->getMessage(), 'insert dealer user & dealer history insert');
        }

        if($success) {
            $transaction->commit();
            return true;

        } else {
           $transaction->rollBack(); 
        }

        return false;

    }

    public function createCompanyRelation() {
        $relation_arr = [ [3,4], [3,5], [6,7] ];
        foreach($relation_arr as $v) {
            $m = new DealerCompanyDealer();
            $m->dealer_company_upline_id = $v[0];
            $m->dealer_company_downline_id = $v[1];
            $m->save();
        }

    }

    public function userProfile($id) {
        $surnames = [
            'Walker','Thompson','Anderson','Johnson','Tremblay','Peltier','Cunningham','Simpson','Mercado','Sellers','Alan','Jodan'];
        $random_name = $surnames[mt_rand(0, sizeof($surnames) - 1)];
        $up = UserProfile::find()->where(['user_id' => $id])->one();
        $up->updateAttributes(["first_name"=>$random_name, "last_name"=> "Test".$id, "gender"=>1, "birthday"=>"08-01-2020"]);

        $u = User::find()->where(['id' => $id])->one();
        $u->email = $random_name."@gmail.com";
        $u->email_status = User::EMAIL_STATUS_VERIFIED;
        $u->save();

     }

    // public function actionUpdateProfile(){
    //     $form = new UpdateProfileForm();
    //     $scenario=null;
    //     $form->scenario = UpdateProfileForm::SCENARIO_DETAIL;
    //     $form->$first_name = "test";
    //     $form->$last_name = "123";
    //     $form->$email = "test123@gmail.com";
    //     $form->$gender = 1;
    //     $form->$birthday = new Date();
    //     $form->update()    
    // }

    public function createCompanyPlan() {
        $plans = InstapPlan::find()->active()->all();
        $companys = DealerCompany::find()->all();
        $plan_id_arr = array_column($plans, 'id');
        $company_id_arr = array_column($companys, 'id');
       
        if(!empty($company_id_arr)) {
            foreach($company_id_arr as $company_id) {
                foreach ($plan_id_arr as $plan_id) {
                    $company_plan = InstapPlanDealerCompany::makeModel($plan_id, $company_id);
                    $company_plan->save();
                }                
            }
        }
    }

    public function createInventory() {
        //$data = [from_compnay, to_company]
        $data = [2, 3, 500];
        $plans = InstapPlan::find()->all();
        $plan_id_arr = array_column($plans, 'id');
        $m = new DealerOrderInventoryOverview();
        // $transaction = Yii::$app->db->beginTransaction();
        foreach ($plan_id_arr as $plan_id) {
            $inventory = DealerOrderInventoryOverview::makeModel($data[1], $data[2], $plan_id);
            $history = DealerInventoryAllocationHistory::makeModel($data[0], $data[1] ,$data[2], $plan_id, DealerInventoryAllocationHistory::ACTION_ALLOCATE);

            if($inventory->save()) {
                $history->save();
            }
        }

    }

  

   
}


