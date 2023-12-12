<?php

namespace common\models;

use common\components\MyCustomActiveRecord;
use common\commands\AddToTimelineCommand;
// use common\models\SysUserToken;
use common\models\query\UserQuery;
use common\commands\SendEmailCommand;
use common\jobs\EmailQueueJob;
use api\components\CustomHttpException;
use Yii;
use yii\behaviors\AttributeBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\IdentityInterface;
use common\components\Utility;
use yii\filters\RateLimitInterface;
use yii\web\UnauthorizedHttpException;


class User extends ActiveRecord implements IdentityInterface, RateLimitInterface
{
    const EMAIL_STATUS_NOT_REGISTERED = "not_registered";
    const EMAIL_STATUS_NOT_VERIFIED = "not_verified";
    const EMAIL_STATUS_VERIFIED = "verified";

    const MOBILE_STATUS_NOT_VERIFIED = "not_verified";
    const MOBILE_STATUS_VERIFIED = "verified";  

    const ACCOUNT_STATUS_SUSPENDED = "suspended";
    const ACCOUNT_STATUS_NORMAL = "normal";
    const ACCOUNT_STATUS_EXCEED_MAX_LOGIN_ATTEMPT = "exceed_max_login_attempt";

    const ROLE_ADMINISTRATOR = 'superadmin';
    const ROLE_DEALER_MANAGER = 'dealer_manager';
    const ROLE_DEALER_ASSOCIATE = 'dealer_associate';
    const ROLE_IP_SUPER_ADMINISTRATOR = 'ip_super_admin';
    const ROLE_IP_ADMINISTRATOR = 'ip_administrator';
    const ROLE_IP_MANAGER = 'ip_manager';
    const ROLE_IP_ADMIN_ASSISTANT = 'ip_admin_assistant';
    const ROLE_USER = 'user';

    const PERMISSION_IP_EDIT = 'ip_editRequest';
    const PERMISSION_IP_APPROVE = 'ip_approveEditRequest';
    const PERMISSION_IP_ACCESS_MY = 'ip_accessMy';
    const PERMISSION_IP_ACCESS_ID = 'ip_accessId';
    const PERMISSION_IP_ACCESS_SG = 'ip_accessSg';
    const PERMISSION_IP_ACCESS_TH = 'ip_accessTh';
    const PERMISSION_IP_ACCESS_VN = 'ip_accessVn';
    const PERMISSION_EDIT_OWN_MODEL = 'editOwnModel';
    const PERMISSION_LOGIN_TO_CMS = 'loginToCms';

    

    const EVENT_AFTER_SIGNUP = 'afterSignup';
    const EVENT_AFTER_LOGIN = 'afterLogin';

    const MAX_LOGIN_ATTEMPTS = 6;

    const SCENARIO_FORCE_LOGOUT = "force_logout";

    const FORCE_LOGOUT_TARGET_SYSTEM = 1;
    const FORCE_LOGOUT_TARGET_COMPANY = 2;
    const FORCE_LOGOUT_TARGET_INDIVIDUAL = 3;

 

    public static function tableName() {
        return '{{%user}}';
    }
    public function rules() {
        return [
            [['mobile_number_full'], 'required'],
            [['mobile_number_full', 'email_admin'], 'unique'],
            [['region_id', 'mobile_calling_code', 'mobile_number', 'mobile_number_full','mobile_status', 'email_status','account_status','notes'], 'string'],
            [['email', 'email_admin'],'email'],
            ['status', 'default', 'value' => MyCustomActiveRecord::STATUS_ENABLED],
            ['status', 'in', 'range' => [MyCustomActiveRecord::STATUS_ENABLED, MyCustomActiveRecord::STATUS_DISABLED]],
           
        ];
    }
    public function behaviors() {
        return [
            TimestampBehavior::class,
        ];
    }
    public function attributeLabels() {
        return [
            'id' => Yii::t('common', 'ID'),
            'provisional_token' => Yii::t('common', 'Provisional Token'),
            'region_code' => Yii::t('common', 'Region Code'),
            'mobile_calling_code' => Yii::t('common', 'Mobile Calling Code'),
            'mobile_number' => Yii::t('common', 'Mobile Number'),
            'mobile_number_full' => Yii::t('common', 'Mobile Number'),
            'mobile_status' => Yii::t('common', 'Mobile Status'),
            'password_salt' => Yii::t('common', 'Password Salt'),
            'password_hash' => Yii::t('common', 'Password Hash'),
            'fcm_token' => Yii::t('common', 'Fcm Token'),
            'email' => Yii::t('common', 'Email'),
            'email_status' => Yii::t('common', 'Email Status'),
            'account_status' => Yii::t('common', 'Account Status'),
            'suspicious_flag' => Yii::t('common', 'Suspicious Flag'),
            'auth_key' => Yii::t('common', 'Auth Key'),
            'access_token' => Yii::t('common', 'Access Token'),
            'notes' => Yii::t('common', 'Notes'),
            'status' => Yii::t('common', 'Status'),
            'created_at' => Yii::t('common', 'Created At'),
            'created_by' => Yii::t('common', 'Created By'),
            'updated_at' => Yii::t('common', 'Updated At'),
            'updated_by' => Yii::t('common', 'Updated By'),
            'active_at' => Yii::t('common', 'Active At'),
            'login_at' => Yii::t('common', 'Login At'),
            'login_attempt' => Yii::t('common', 'Login Attempt'),
        ];
    }
    public function can($role_type) {
        // use Yii::$app->authManager->checkAccess($user->id, $role) instead
        $roles = Yii::$app->authManager->getRolesByUser($this->id);
        $isRole = false;
        foreach ($roles as $key => $value) {
            if ($key == $role_type) {
                $isRole = true;
            }
        }
        return $isRole;
    }

    public function getUserProfile() {
        return $this->hasOne(UserProfile::class, ['user_id' => 'id']);
    }

    public function getUserPlans() {
        return $this->hasOne(UserPlan::class, ['user_id' => 'id']);
    }

    public function getAccessToken() {
        $model = SysOAuthAccessToken::find()->andWhere(['user_id' => $this->id])->one();
        if ($model) {
            return $model->token;
        }
        return null;  
    }
    public function validatePassword($password) {
        return Yii::$app->getSecurity()->validatePassword($password, $this->password_hash);
    }
    public function generateSalt() {
        // https://stackoverflow.com/questions/38716613/generate-a-single-use-token-in-php-random-bytes-or-openssl-random-pseudo-bytes
        $rand = random_bytes(3);
        $this->password_salt = bin2hex($rand);
        // print_r("random_bytes: " . $this->salt);
        // exit();
    }
    public function setPassword($password) {
        $salted_password = $password . $this->password_salt;
        $this->password_hash = Yii::$app->getSecurity()->generatePasswordHash($salted_password);
    }
    public function afterSignup(array $profileData = []) {
        $this->refresh();
        Yii::$app->commandBus->handle(new AddToTimelineCommand([
            'category' => 'user',
            'event' => 'signup',
            'data' => [
                'public_identity' => $this->getPublicIdentity(),
                'user_id' => $this->getId(),
                'created_at' => $this->created_at
            ]
        ]));
        $profile = new UserProfile();
        //$profile->locale = Yii::$app->language;
        //$profile->load($profileData, '');
        $this->link('userProfile', $profile);
        $this->trigger(self::EVENT_AFTER_SIGNUP);
        // Default role
        $auth = Yii::$app->authManager;
        $auth->assign($auth->getRole(User::ROLE_USER), $this->getId());
    }
    //IdentityInterface::getAuthKey
    public function getAuthKey() {
        return $this->auth_key;
    }
    public function validateAuthKey($authKey) {
        return $this->getAuthKey() === $authKey;
    }
    public function getId() {
        return $this->getPrimaryKey();
    }
    public function getFormatMobileNumber(){
        return "+" . $this->mobile_calling_code . " " . $this->mobile_number;
    }    
    public function getPublicIdentity() {        
        if ($this->userProfile && $this->userProfile->first_name) {
            return utf8_decode($this->userProfile->first_name);
        } 
        if ($this->email) {
            return $this->email;
        }       
        return $this->getFormatMobileNumber();
    }
    public static function isUserLoggedIn($user_id) {
        $model = SysOAuthAccessToken::find()->Where(['user_id'=>$user_id])->one();
        if ($model == null){
            // no access token means not logged in
            return false;
        } else {
            // have access token
            return true;
        }
    }
    public static function findIdentity($id) {
        return static::find()
            ->active()
            ->andWhere(['id' => $id])
            ->one();
    }
    public static function find() {
        return new UserQuery(get_called_class());
    }
    public static function findByFullMobileNumber($mobile) {
        //return static::find()->active()->andWhere(['mobile_number_full' => $mobile])->one();
        return static::find()->andWhere(['mobile_number_full' => $mobile])->one();
    }
    public static function findByAdminEmail($email_admin) {
        //return static::find()->active()->andWhere(['mobile_number_full' => $mobile])->one();
        return static::find()->andWhere(['email_admin' => $email_admin])->one();
    }
    public static function findIdentityByAccessToken($token, $type = null) {
        $access_token = SysOAuthAccessToken::find()->andWhere(['token' => $token])->one();
        if ($access_token) {
            // -1 is not going to expire
            if ($access_token->expire_at != -1 && $access_token->expire_at < time()) {
                throw new UnauthorizedHttpException("Access token Expired");
            }
            return static::find()->andWhere(['id' => $access_token->user_id])->one();
        }
        return null;        
    }
    public static function revokeAccessToken($user_id) {
        $user = self::find()->andWhere(['id' => $user_id])->one();
        if ($user) {
            SysOAuthAccessToken::deleteAll('user_id = :user_id', [':user_id' => $user_id]);
        }
    }
    public static function revokeFcmToken($user_id) {
        $user = self::find()->andWhere(['id' => $user_id])->one();
        if ($user) {
            $user->updateAttributes(["fcm_token"=>""]);
        }
    }
    public static function generateProvisionalToken(){
        $token = "";
        $unique = true;
        do {
            $token = Utility::randomToken(64);
            $user = self::find()->andWhere(['provisional_token' => $token])->one();
            $unique = ($user == null);
        } while (!$unique);
        return $token;
    }
    public function getRoleArrayById($user_id) {
        $auth = Yii::$app->authManager;
        $item = $auth->getRolesByUser($user_id);
        $role_names = array_values($item);
        $arr = [];
        for($i = 0; $i< count($role_names); $i++){
            array_push($arr, $role_names[$i]->name);
        }
        return $arr;
    }
    public static function mobileStatus(){
        return[ 
            self:: MOBILE_STATUS_NOT_VERIFIED => Yii::t('common','NOT VERIFIED'),
            self:: MOBILE_STATUS_VERIFIED => Yii::t('common','VERIFIED'),
        ];
    }
    public static function accountStatus(){
        return[
            self::ACCOUNT_STATUS_SUSPENDED => Yii::t('common','SUSPENDED'),
            self::ACCOUNT_STATUS_NORMAL => Yii::t('common','NORMAL'),
        ];
    }
    public static function emailStatus(){
        return[
            self::EMAIL_STATUS_NOT_VERIFIED => Yii::t('common','NOT VERIFIED'),
            self::EMAIL_STATUS_NOT_REGISTERED => Yii::t('common','NOT REGISTERED'),
            self::EMAIL_STATUS_VERIFIED => Yii::t('common','VERIFIED'),
        ];
    }
    //Roles
    public static function allRoles() {
        return[
            self::ROLE_ADMINISTRATOR => Yii::t('common', 'Super Admin'),
            self::ROLE_DEALER_MANAGER => Yii::t('common', 'Dealer Manager (Able to assign new Dealer Associate, request/allocate/activate stocks from inventory, and sell plans)'),
            self::ROLE_DEALER_ASSOCIATE => Yii::t('common', 'Dealer Associate (Able to view stocks status from inventory, and sell plans)'),
            self::ROLE_IP_SUPER_ADMINISTRATOR => Yii::t('common', 'IP Super Admin (Able to view all countries\' data)'),
            self::ROLE_IP_ADMINISTRATOR => Yii::t('common', 'IP Administrator (Able to assign new IP Manager and Admin Assistant)'),
            self::ROLE_IP_MANAGER => Yii::t('common', 'IP Manager (Able to approve impromptu changes to policy details, submitted by IP Admin Assistant)'),
            self::ROLE_IP_ADMIN_ASSISTANT => Yii::t('common', 'IP Admin Assistant (Able to process policy registrations and claims, and submission of impromptu changes to policy details for approval)'),
            self::ROLE_USER => Yii::t('common', 'User'),
        ];
    }
    public static function allRoleNames() {
        return[
            self::ROLE_ADMINISTRATOR => Yii::t('common', 'Super Admin'),
            self::ROLE_DEALER_MANAGER => Yii::t('common', 'Dealer Manager'),
            self::ROLE_DEALER_ASSOCIATE => Yii::t('common', 'Dealer Associate'),
            self::ROLE_IP_SUPER_ADMINISTRATOR => Yii::t('common', 'IP Super Admin'),
            self::ROLE_IP_ADMINISTRATOR => Yii::t('common', 'IP Administrator'),
            self::ROLE_IP_MANAGER => Yii::t('common', 'IP Manager'),
            self::ROLE_IP_ADMIN_ASSISTANT => Yii::t('common', 'IP Admin Assistant'),
            self::ROLE_USER => Yii::t('common', 'User'),
        ];
    }
    public static function dealerUserRoles() {
        return[
            self::ROLE_DEALER_MANAGER => self::allRoles()[self::ROLE_DEALER_MANAGER],
            self::ROLE_DEALER_ASSOCIATE => self::allRoles()[self::ROLE_DEALER_ASSOCIATE],
        ];
    }
    public static function ipStaffRoles() {
        return[
            self::ROLE_IP_SUPER_ADMINISTRATOR => self::allRoles()[self::ROLE_IP_SUPER_ADMINISTRATOR],
            self::ROLE_IP_ADMINISTRATOR => self::allRoles()[self::ROLE_IP_ADMINISTRATOR],
            self::ROLE_IP_MANAGER => self::allRoles()[self::ROLE_IP_MANAGER],
            self::ROLE_IP_ADMIN_ASSISTANT => self::allRoles()[self::ROLE_IP_ADMIN_ASSISTANT],
        ];
    }

    public static function getMobileCallingCode() {
      return [
          60 => "+60 (MY)", // Malaysia
          62 => "+62 (ID)", // Indonesia
          65 => "+65 (SG)", // Singapore
          66 => "+66 (TH)", // Thailand
          84 => "+84 (VN)", // Vietnam
      ];
    }

    //Permission
    public static function allPermission() {
        return[
            self::PERMISSION_IP_EDIT => Yii::t('common', 'Edit Model'),
            self::PERMISSION_IP_APPROVE => Yii::t('common', 'Approve Edit'),
            self::PERMISSION_IP_ACCESS_MY => Yii::t('common', 'Access Malaysia'),
            self::PERMISSION_IP_ACCESS_ID => Yii::t('common', 'Access Indonesia'),
            self::PERMISSION_IP_ACCESS_SG => Yii::t('common', 'Access Singapore'),
            self::PERMISSION_IP_ACCESS_TH => Yii::t('common', 'Access Thailand'),
            self::PERMISSION_IP_ACCESS_VN => Yii::t('common', 'Access Vietnam'),
            self::PERMISSION_EDIT_OWN_MODEL => Yii::t('common', 'edit own model'),
            self::PERMISSION_LOGIN_TO_CMS => Yii::t('common', 'login CMS'),
            //self::PERMISSION_DEALER_ACTIVATE_INVENTORY => Yii::t('common', 'Permission to ACTIVATE new stocks in inventory'),
        ];
    }
    public static function dealerUserPermissions() {
        return[
            self::PERMISSION_DEALER_ACTIVATE_INVENTORY => self::allPermission()[self::PERMISSION_DEALER_ACTIVATE_INVENTORY]
        ];
    }    
    public static function ipStaffPermissions() {
        return[
            self::PERMISSION_IP_EDIT => self::allPermission()[self::PERMISSION_IP_EDIT],
            self::PERMISSION_IP_APPROVE => self::allPermission()[self::PERMISSION_IP_APPROVE],
            "none" => "None"
        ];
    }
    public static function countryAccessPermissions(){
        return[
            self::PERMISSION_IP_ACCESS_SG => self::allPermission()[self::PERMISSION_IP_ACCESS_SG],
            self::PERMISSION_IP_ACCESS_MY => self::allPermission()[self::PERMISSION_IP_ACCESS_MY],
            self::PERMISSION_IP_ACCESS_ID => self::allPermission()[self::PERMISSION_IP_ACCESS_ID],
            self::PERMISSION_IP_ACCESS_TH => self::allPermission()[self::PERMISSION_IP_ACCESS_TH],
            self::PERMISSION_IP_ACCESS_VN => self::allPermission()[self::PERMISSION_IP_ACCESS_VN],
        ];
    }
    public function getGrantedCountryAccessPermissions(){
        $items = Yii::$app->authManager->getPermissionsByUser($this->id);
        $p = [];
        if(ArrayHelper::keyExists(User::PERMISSION_IP_ACCESS_SG, $items, false)) array_push($p, User::PERMISSION_IP_ACCESS_SG);
        if(ArrayHelper::keyExists(User::PERMISSION_IP_ACCESS_MY, $items, false)) array_push($p, User::PERMISSION_IP_ACCESS_MY);
        if(ArrayHelper::keyExists(User::PERMISSION_IP_ACCESS_ID, $items, false)) array_push($p, User::PERMISSION_IP_ACCESS_ID);
        if(ArrayHelper::keyExists(User::PERMISSION_IP_ACCESS_TH, $items, false)) array_push($p, User::PERMISSION_IP_ACCESS_TH);
        if(ArrayHelper::keyExists(User::PERMISSION_IP_ACCESS_VN, $items, false)) array_push($p, User::PERMISSION_IP_ACCESS_VN);
        return $p;
    }

    public function getUserDetails(){
        $data = (object) [];
        $data->user_id = $this->userProfile->user_id;
        $data->email  = $this->email;
        $data->mobile_number_full = $this->mobile_number_full;
        $data->first_name  = utf8_decode($this->userProfile->first_name);
        $data->last_name  = utf8_decode($this->userProfile->last_name);
        // $data->avatar_url = $this->userProfile->avatar;
        $data->avatar_url = $this->userProfile->getAvatarByRegion($this);
        $data->birthday = $this->userProfile->birthday;
        $data->gender = $this->userProfile->gender;
        $data->account_status = $this->account_status;
        $data->email = $this->email;
        $data->active_at = $this->active_at;
        $data->login_at = $this->login_at;
        $data->created_at = $this->created_at;
        $data->email_status = $this->email_status;
        $data->role_type = $this->displayRoleType();
        $data->provisional_token = $this->provisional_token;
        $data->profile_completion_status = $this->userProfile->completionStatus;
        return $data;
    }
    public function createAndSendActiviationTokenEmail()
    {            
        // $token = new SysUserToken();
        $u = Yii::$app->user;
        $token = SysUserToken::makeModel($u,SysUserToken::TYPE_EMAIL_ACTIVATION);
        // print_r($token->attributes);
        if(!$token->save()){
            // print_r("save success");
            $message = "Can't save token";
            throw new CustomHttpException(Utility::jsonifyError("token", $message , CustomHttpException::KEY_UNEXPECTED_ERROR), CustomHttpException::UNPROCESSABLE_ENTITY);
        } 
        // print_r($this->email);
        Yii::$app->queue->delay(0)->push(new EmailQueueJob([
            'subject' => Yii::t('frontend', '{app-name} | Email Verification', ['app-name'=>Yii::$app->name]),
            'view' => 'verifyAccount',
            'to' => $this->email,
            'params' => [
                'user' => $this->email,
                'token' => $token->token
            ]
        ]));
        return true;
        // Instaprotection | Email Verification
    }    
    public static function sendTelegramBotMessage($msg){
        $apiToken = env('TELEGRAM_BOT_TOKEN');

        $text = "<b>" . Yii::$app->name . "</b>\n\n\n";
        $text .= $msg;
        $text .= "\n\n\n<i>Message generated on: \n" . Yii::$app->formatter->asDatetime(time()) . "</i>";

        $data = [
            'chat_id' => env('TELEGRAM_GROUP_ID'),
            'text' => $text,
            'parse_mode' => "html"
        ];

        $response = file_get_contents("https://api.telegram.org/bot$apiToken/sendMessage?" . http_build_query($data));
        // Do what you want with result
        // print_r($response);
    }  



    public function displayRoleType() {
        $roles = Yii::$app->authManager->getRolesByUser($this->id);
        $str = "";
        foreach ($roles as $key => $value) {
            $str .= $key . ",";
        }
        $str = substr($str, 0, -1);
        return $str;
    }

    public static function getUserNotIpStaffConcatWithUserName() {
        $ip_staffs_ids = self::find()->select('user.id')->join('LEFT JOIN','rbac_auth_assignment','rbac_auth_assignment.user_id = id')->andWhere(['user.region_id' => Yii::$app->session->get('region_id')])->andWhere(['in', 'rbac_auth_assignment.item_name', [User::ROLE_IP_ADMINISTRATOR, User::ROLE_IP_MANAGER, User::ROLE_IP_ADMIN_ASSISTANT, User::ROLE_IP_SUPER_ADMINISTRATOR]])->distinct()->all();

        $notIpStaff = self::find()->andWhere(['not in','id', $ip_staffs_ids])->andWhere(['region_id' => Yii::$app->session->get('region_id')])->all();

        $concatUser = ArrayHelper::map($notIpStaff, 'id', function($model) {
            $fullname = "";
            $seperator = (empty($model->userProfile->first_name) && empty($model->userProfile->last_name)) ? "" : " - ";
            if(isset($model->userProfile->first_name) && isset($model->userProfile->last_name)) {
                $fullname = $model->userProfile->first_name.$model->userProfile->last_name;
            }
            return $model->mobile_number_full.$seperator.$fullname;
        });
        return $concatUser;
    }

    //*********** html layout ***********  
    

    public function getRoleLayout() {
        $roles = Yii::$app->authManager->getRolesByUser($this->id);
        $html = "";
        foreach ($roles as $key => $value) {
            $html .= "<div class='role'>" . self::allRoles()[$key] . "</div>";
        }
        return $html;
    }

    public static function getRoleLayoutById($user_id) {
        //user/update-ip-staff?id=131
        //dealer-user/update?id=153
        $link = "";
        $roles = Yii::$app->authManager->getRolesByUser($user_id);
        $html = "";
        foreach ($roles as $key => $value) {
            // print_r($key);exit();
            if(in_array($key, array_keys(self::ipStaffRoles()))) {
                $link = Url::to(['user/update-ip-staff', 'id' => $user_id]);
                $html .= "<span class='role'><a href='$link'>" . self::allRoleNames()[$key] . "</a></span>";
            } else if(in_array($key, array_keys(self::dealerUserRoles()))) {
                $link = Url::to(['dealer-user/update', 'id' => $user_id]);
                $html .= "<span class='role'><a href='$link'>" . self::allRoleNames()[$key] . "</a></span>";
            }else {
                $html .= "<span class='role'>" . self::allRoleNames()[$key] . "</span>";
            }
        }
        return $html;
    }

    public static function getPermissionLayoutById($user_id) {
        $roles = Yii::$app->authManager->getPermissionsByUser($user_id);
        $html = "";
        foreach ($roles as $key => $value) {
            $html .= "<span class='role'>" . self::allPermission()[$key] . "</span>";
        }
        return $html;
    }

    public static function forceLogoutAll() {
        User::updateAll(['fcm_token' => ""]);
        SysOAuthAccessToken::deleteAll();

        return true;
    }

    public static function forceLogoutByUserId($user_id) {
        User::revokeAccessToken($user_id);
        return true;
    }

    public static function forceLogoutByCompany($dealer_company_id) {
        $dealer_users = DealerUser::find()->andWhere(['dealer_company_id'=> $dealer_company_id])->all();
        if($dealer_users){
            foreach ($dealer_users as $dealer_user) {
                User::revokeAccessToken($dealer_user->user_id);
            }
        }
        return true;
    }

     //#####################
    //ratelimiter interface
    //#####################
    //RestControllerBase
    public function getRateLimit($request, $action) {
        //return [$this->rateLimit,1];
        //1 time per 30 secs
        // print_r($request);exit();
        return [1,30];
    }

    public function loadAllowance($request, $action)
    {
        // print_r($request);exit();
        $endpoint = $action->controller->id ."/" . $action->id;
        //$endpoint = "all";
        $rate = ApiRateLimiter::findEntry($this->id, $endpoint);
        
        if ($rate) {        
            return [$rate->allowance, $rate->allowance_updated_at];
        } else {
            $rate = new ApiRateLimiter();
            $rate->user_id = $this->id;
            $rate->endpoint = $endpoint;
            $rate->allowance = 0;
            $rate->allowance_updated_at = time();
            $rate->save();
        }
    }

    public function saveAllowance($request, $action, $allowance, $timestamp)
    {
        $endpoint = $action->controller->id ."/" . $action->id;
        //$endpoint = "all";
        $rate = ApiRateLimiter::findEntry($this->id, $endpoint);
        if ($rate) {        
            $rate->allowance = $allowance;
            $rate->allowance_updated_at = $timestamp;
            $rate->save(false);
        }        
    }

}
