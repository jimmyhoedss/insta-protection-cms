<?php

namespace console\controllers;

use Yii;
use yii\base\Module;
use yii\console\Controller;
use yii\console\Exception;

use yii\helpers\Console;
use common\commands\SendSmsCommand;
use common\commands\SendEmailCommand;
use common\models\User;
use common\models\DealerCompany;
use common\models\DealerUser;
use common\models\DealerUserHistory;
use common\models\form\RegistrationForm;
// require_once '/path/to/Faker/src/autoload.php';
/**
 * @author Oh's test case
 */
class TestCaseController extends Controller
{   

    public function actionTest() {
        $faker = Faker\Factory::create();

        // generate data by accessing properties
        echo $faker->name;
    }

    public static function regionToCallingcCode() {
      return [
          'MY' => 60, // Malaysia
          'ID' => 62, // Indonesia
          'SG' => 65, // Singapore
          'TH' => 66, // Thailand
          'VN' => 84, // Vietnam
      ];
    }

    /**
     * @param string $region_id integer $number_of_user string $mobile_number [note: if $number_of_user > 1 will auto become random generate mobile number]
     */
    public function actionGenerateUser($region_id ='SG', $number_of_user=1, $mobile_number=null) {
        
        $code = self::regionToCallingcCode()[$region_id];
        try{
            for ($i=0; $i < $number_of_user; $i++) { 
                $form = new RegistrationForm();
                $form->region_id = (string)$region_id;
                $form->mobile_calling_code = (string)$code;
                $form->mobile_number =  ($mobile_number && $number_of_user <= 1)? $mobile_number : (string) random_int(8000000,99999999);
                $form->mobile_number_full = $form->mobile_calling_code.$form->mobile_number;
                if($form->register()) {
                    Console::output("User {$code} {$form->mobile_number} with region {$region_id} is generated ");
                } else {
                    $code = $form->getErrors();
                     Console::output("Fail to generate User {$code} {$mobile_number} with region {$region_id} ");
                }

            }

        } catch (\Exception $e) {
            $success = false;
            Yii::warning($e->getMessage(), 'insert user test');
        }
 
    }

    public function actionGenerateDefaultUsers() {
        $user_arr = [
            [1, "SG", "65", "97479576"],//super admin
            [2 ,"SG", "65", "93732061"],//super admin
            [3 ,"SG", "65", "91372701"],//super admin
            [4 ,"SG", "65", "85712472"],//super admin
            [5 ,"SG", "65", "12345678"],//ip admin
        ];

        try{

            foreach($user_arr as $v){
                $form = new RegistrationForm();
                $form->region_id = $v[1];
                $form->mobile_calling_code = $v[2];
                $form->mobile_number = $v[3];
                $form->mobile_number_full = $v[2].$v[3];
                $form->register();
                Console::output("User {$code} {$form->mobile_number} with region {$region_id} is generated ");
                // self::userProfile($v[0]);
            }

        } catch (\Exception $e) {
            $success = false;
            Console::output("Fail to generate user.");

            Yii::warning($e->getMessage(), 'insert user test');
        }
    }

    /**
     * @param integer $mode [1: stockpile, 2 : ad-hoc] default: 1

        $allocation_mode [1: none, 2 : allocate_only, 3: allocate_or_activate, 4 : activate_only] default: 1
     */
    public function actionGenerateCompany($region_id ='SG', $number_of_company=1, $company_mode=1, $allocation_mode=1) {
        
        // $code = self::regionToCallingcCode()[$region_id];
        $connection = Yii::$app->getDb();
        $mode = ($company_mode == 1) ? DealerCompany::INVENTORY_MODE_STOCKPILE : DealerCompany::INVENTORY_MODE_AD_HOC;
        $mode_allocate = ($allocation_mode == 1) ? DealerCompany::ALLOCATION_MODE_NONE :($allocation_mode == 2) ? DealerCompany::ALLOCATION_MODE_ALLOCATE : ($allocation_mode == 3) ? DealerCompany::ALLOCATION_MODE_ALLOCATE_OR_ACTIVATE : DealerCompany::ALLOCATION_MODE_ACTIVATE ;

        $company_names = [
            '3Com Corp Ptd Lte','3M Company Ptd Lte','Abbott Laboratories Ptd Lte','Johnson Ptd Lte','Tremblay Ptd Lte','Peltier Ptd Lte','Cunningham Ptd Lte','Simpson Ptd Lte','Mercado Ptd Lte','Sellers Ptd Lte','Alan Ptd Lte','Jodan Ptd Lte'];

        $company_address = [
            'Building 225-4N-14 St. Paul, MN 55144-1000','Pomona, CA 91768','Salt Lake City, UT 84411','4615 Fifth Ave.Pittsburgh, PA 15213','5383 Hollister Ave.Suite 200 Santa Barbara, CA 93111','P.O. Box 3751 Scottsdale, AZ 85271','1 Linden Ct.Bloomfield, CT 06002','14394 E. Evans Ave.Aurora, CO 80014','240 Edward St.Aurora, ON L4G 3S9 CANADA','3501 Market St.Philadelphia, PA 19104','100 North Rd.P.O. Box 300 McElhattan, PA 17748','327 E. Gundersen Dr. Carol Stream, IL 60188'];

        try{
            for ($i=0; $i < $number_of_company; $i++) { 
                $random_company_name = $company_names[mt_rand(0, sizeof($company_names) - 1)];
                $random_company_address = $company_address[mt_rand(0, sizeof($company_address) - 1)];
                $str_suffle = str_shuffle("1234456");
                $connection->createCommand()->insert('dealer_company', [
                    'region_id' => $region_id,
                    'business_registration_number' => "BS".(string) random_int(8000,99999),
                    'business_name' => $str_suffle.$random_company_name,
                    'business_address' => $random_company_address,
                    'business_zip_code' => (string) random_int(80000,99999),
                    'business_phone' => $region_id.(string) random_int(8000000,99999999),
                    'business_email' => $str_suffle.trim($random_company_name).'@email.com',
                    'sp_inventory_order_mode' => $mode,
                    'sp_inventory_allocation_mode' => $mode_allocate,
                    'notes' => 'Test company generated',
                    'business_city' => 'Singapore',
                    'business_state' => 'Singapore',
                    'business_country' => 'Singapore',
                    'business_contact_person' => 'Test',
                    'created_by' => 1,
                    'created_at' => time(),
                    'updated_by' => 1,
                    'updated_at' => time(),
                ])->execute();

            Console::output("{$random_company_name}  is generated");
            }

        } catch (\Exception $e) {
            $success = false;
            Yii::warning($e->getMessage(), 'insert user test');
        }
 
    }

    /**
     * @param integer $mode [1: stockpile, 2 : ad-hoc] default: 1
     */
    public function actionListCompany($mode = 1) {
        $inv_mode = ($mode==1) ? DealerCompany::INVENTORY_MODE_STOCKPILE : DealerCompany::INVENTORY_MODE_AD_HOC;
        $companys = DealerCompany::find()->andWhere(['sp_inventory_order_mode' => $inv_mode])->all();
        if(!empty($companys)) {
            Console::output("Compny List ({$inv_mode}):");
            foreach ($companys as $c) {
                Console::output("id: {$c->id}, name: {$c->business_name}, mode: {$c->sp_inventory_order_mode} , mode_allocate: {$c->sp_inventory_allocation_mode}");
            }
        } else {
                Console::output("No company found");

        }

 
    }


    /**
     * @param  integer $company_id integer $role string $region_id integer $number_of_user role : [1: dealer_manager, 2: dealer_associate]  
    */
      //[run yii test-case/list-company before assign]
    public function actionGenerateDealerUser($company_id, $role=1, $region_id='SG', $number_of_user = 1) {
        if(!$company_id) {
            throw new Exception("company_id cannotbe empty, run yii test-case/list-company to display available company");
        }
        $connection = Yii::$app->getDb();
        $role_name = ($role==1) ? User::ROLE_DEALER_MANAGER : User::ROLE_DEALER_ASSOCIATE;
        $code = self::regionToCallingcCode()[$region_id];
        $mobile_number = "";
        $auth = Yii::$app->authManager;
        try{
            for ($i=0; $i < $number_of_user; $i++) { 
                //generate random user
                $form = new RegistrationForm();
                $form->region_id = (string)$region_id;
                $form->mobile_calling_code = (string)$code;
                $form->mobile_number =  ($mobile_number && $number_of_user <= 1)? $mobile_number : (string) random_int(8000000,99999999);
                $form->mobile_number_full = $form->mobile_calling_code.$form->mobile_number;
                $user = $form->register();
                
                if($user) {
                    $user_id = $user->id;
                    $connection->createCommand()->insert('dealer_user', [
                        'user_id' => $user_id,
                        'dealer_company_id' => $company_id,
                        'created_by' => 1,
                        'created_at' => time(),
                        'updated_by' => 1,
                        'updated_at' => time(),
                    ])->execute();
                    Console::output("Dealer Staff {$code} {$user->mobile_number} with region {$region_id} is generated");
                } else {
                    $code = $form->getErrors();
                     Console::output("Fail to generate Dealer Staff {$code} {$user->mobile_number} with region {$region_id} ");
                }
            }

        } catch (\Exception $e) {
            $success = false;
            Yii::warning($e->getMessage(), 'insert dealer test-case');
        }
 
    }

    /**
     * @param  integer $company_id string $region_id 
    */
    public function actionGenerateDefaultDealerUser($company_id, $role=1, $region_id='SG') {
        if(!$company_id) {
            throw new Exception("company_id cannotbe empty, run yii test-case/list-company to display available company");
        }
        $connection = Yii::$app->getDb();
        $role_name = ($role==1) ? User::ROLE_DEALER_MANAGER : User::ROLE_DEALER_ASSOCIATE;
        $code = self::regionToCallingcCode()[$region_id];
        // $mobile_number = "";
        $auth = Yii::$app->authManager;

        $mobile_number = ['00000001', '00000002', '00000003'];
        try{
            for ($i=0; $i < count($mobile_number); $i++) { 
                //generate random user
                $form = new RegistrationForm();
                $form->region_id = (string)$region_id;
                $form->mobile_calling_code = (string)$code;
                $form->mobile_number =  $mobile_number[$i];
                $form->mobile_number_full = $form->mobile_calling_code.$form->mobile_number;
                $user = $form->register();
                
                if($user) {
                    $user_id = $user->id;
                    $connection->createCommand()->insert('dealer_user', [
                        'user_id' => $user_id,
                        'dealer_company_id' => $company_id,
                        'created_by' => 1,
                        'created_at' => time(),
                        'updated_by' => 1,
                        'updated_at' => time(),
                    ])->execute();
                    Console::output("Dealer Staff {$code} {$user->mobile_number} with region {$region_id} is generated");
                } else {
                    $code = $form->getErrors();
                     Console::output("Fail to generate Dealer Staff {$code} {$user->mobile_number} with region {$region_id} ");
                }
            }

        } catch (\Exception $e) {
            $success = false;
            Yii::warning($e->getMessage(), 'insert dealer test-case');
        }
 
    }

    /**
     * @param  string $mobile_number  
    */
    public function actionVerifyEmailExcept($mobile_number=null) {
        // $email_status = ($status ==1)? User::EMAIL_STATUS_VERIFIED:User::EMAIL_STATUS_NOT_VERIFIED;
        $user = "";
        if(!$mobile_number) {
            $users = User::find()->all();
        } else {
            $users = User::find()->where('mobile_number != :mobile_number', ['mobile_number'=> $mobile_number])->all();
            $except_user = User::find()->where('mobile_number = :mobile_number', ['mobile_number'=> $mobile_number])->one();
            $except_user->updateAttributes(['email_status' => User::EMAIL_STATUS_NOT_VERIFIED]);
        }

        foreach ($users as $v) {
            $u = User::find()->where(['id' => $v->id])->one();
            $u->updateAttributes(['email_status' => User::EMAIL_STATUS_VERIFIED]);
        }

        if(!$mobile_number){ 
            $all_users = User::find()->count();
            $users_verified = User::find()->where('email_status = :email_status', ['email_status'=> User::EMAIL_STATUS_VERIFIED])->count();
            if($all_users == $users_verified) {
                Console::output("All email is verified");
            }
        } else {
            Console::output("All email is verified except $mobile_number");
        }


    }

}
