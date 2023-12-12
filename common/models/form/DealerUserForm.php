<?php
namespace common\models\form;

use common\models\User;
use yii\base\InvalidParamException;
use yii\base\Model;
use Yii;
use yii\web\JsExpression;
use common\components\MyCustomBadRequestException;
use common\models\DealerCompany;
use common\models\DealerUser;
use common\models\DealerOrder;
use common\models\DealerOrderAdHoc;
use common\models\DealerUserHistory;
use common\models\DealerCompanyDealer;


use common\models\fcm\FcmDealerAddStaff;
use common\models\fcm\FcmDealerDeleteStaff;
use api\components\CustomHttpException;
use common\components\Utility;
use common\components\MyCustomActiveRecord;




/**
 * Password reset form
 */
class DealerUserForm extends Model
{
    public $dealer_staff_mobile;
    public $dealer_staff_id;

    const SCENARIO_ADD_STAFF = "scenario_add_staff";
    const SCENARIO_DELETE_STAFF = "scenario_delete_staff";
    

    public function rules()
    {
        return [
            [['dealer_staff_mobile'], 'required', 'on' => self::SCENARIO_ADD_STAFF],
            [['dealer_staff_id'], 'required', 'on' => self::SCENARIO_DELETE_STAFF],
            [['dealer_staff_mobile'], 'string'],
            [['dealer_staff_id'], 'integer'],
        ];
    }
    
 
    public function attributeLabels()
    {
        return [
            'dealer_staff_mobile' => Yii::t('common', 'Dealer Staff Mobile Number'),
            'dealer_staff_id' => Yii::t('common', 'Staff ID'),
        ];
    }


   public function addStaff(){
        $success = false;
        $dealerStaff = User::findByFullMobileNumber($this->dealer_staff_mobile);
        $dealer_region_id = Yii::$app->user->identity->region_id;
        if($dealerStaff){
            $transaction = Yii::$app->db->beginTransaction();
            try{

                $staff = DealerUser::find()->where(['user_id' => $dealerStaff->id])->one();
                $dealer = DealerUser::getDealerFromUserId(Yii::$app->user);
                if ($staff) {
                    //check staff country
                    if($staff->user->region_id !== $dealer_region_id) {
                        $this->addError('dealer_staff_mobile', Yii::t('common', 'This user does not exist.'), CustomHttpException::UNPROCESSABLE_ENTITY);
                        return null;
                    }
                    if($staff->status === MyCustomActiveRecord::STATUS_DISABLED) {
                        //if disabled den update company and enabled
                        $staff->dealer_company_id = $dealer->id;
                        $staff->status = MyCustomActiveRecord::STATUS_ENABLED;
                        $auth = Yii::$app->authManager;
                        $auth->assign($auth->getRole(User::ROLE_DEALER_ASSOCIATE), $dealerStaff->id);
                        $dh = DealerUserHistory::makeModel($dealerStaff->id, $dealer->id, DealerUserHistory::ACTION_ADD_ROLE, User::ROLE_DEALER_ASSOCIATE);
                        if($dh->save() && $staff->save()){
                            $success = true;
                        }

                    } else {
                        //if staff status is active
                        $this->addError('dealer_staff_mobile', Yii::t('common', 'Staff already in another company.'), CustomHttpException::UNPROCESSABLE_ENTITY);
                        return null;
                    }

                } else {
                    //add new staff
                    $staff = DealerUser::makeModel($dealer, $dealerStaff->id);
                    $staff->scenario = DealerUser::SCENARIO_API;
                    if($staff->save()){
                        $auth = Yii::$app->authManager;
                        $auth->assign($auth->getRole(User::ROLE_DEALER_ASSOCIATE), $dealerStaff->id);
                        $dh = DealerUserHistory::makeModel($dealerStaff->id, $dealer->id, DealerUserHistory::ACTION_ADD_ROLE, User::ROLE_DEALER_ASSOCIATE);
                        $dh->save();
                        $success = true;
                        
                    }
                }
                

            } catch (yii\db\IntegrityException $e) {
                $transaction->rollback();
                Yii::error($e->getMessage(), 'DealerUserForm add staff');
            } catch ( \Exception $e ) {
                $transaction->rollback();
                Yii::error($e->getMessage(), 'DealerUserForm add staff');
            }

            if($success) {
                $transaction->commit();
                $dm = DealerUser::getAllDealerManager($dealer->id);
                for ($i=0; $i < count($dm) ; $i++) {
                    $fcm = new FcmDealerAddStaff($dm[$i], $staff, User::ROLE_DEALER_MANAGER);
                    $fcm->send();
                }
                //notify to added staff
                $fcm = new FcmDealerAddStaff($staff, $staff, User::ROLE_DEALER_ASSOCIATE);
                $fcm->send();
                return $staff;

            } else {
                $transaction->rollback();
                $this->addError("", Yii::t('common', 'Unable to add staff.'), CustomHttpException::UNPROCESSABLE_ENTITY);
                return null;
            } 
        } else {
            $this->addError('dealer_staff_mobile', Yii::t('common', 'This user does not exist.'), CustomHttpException::BAD_REQUEST);
            return null;
        }
    }

    public function deleteStaff(){
        $du = DealerUser::find()->where(['user_id' => $this->dealer_staff_id])->active()->one();
        $dealer = DealerUser::getDealerFromUserId(Yii::$app->user);
        $success = false;
        if($du){
            $transaction = Yii::$app->db->beginTransaction();
            try{
                $duId = $du->user_id;
                $du_auth = Yii::$app->authManager;
                $item = $du_auth->getRolesByUser($duId);
                $role_names = array_values($item);
                $arr = [];
                for($i = 0; $i< count($role_names); $i++){
                    array_push($arr, $role_names[$i]->name);
                }
                // print_r($arr);
                // exit();
                for($i=0; $i<count($arr); $i++){
                    if($arr[$i] == User::ROLE_DEALER_MANAGER || $arr[$i] == User::ROLE_ADMINISTRATOR){
                        $this->addError("", Yii::t("common","Unable to delete") . str_replace('_', ' ', $arr[$i]), CustomHttpException::UNPROCESSABLE_ENTITY);
                        return null;
                    }
                }
                $dh = DealerUserHistory::makeModel($duId, $du->dealer_company_id, DealerUserHistory::ACTION_REMOVE_ROLE, User::ROLE_DEALER_ASSOCIATE);
                if($dh->save()){
                    for($i=0; $i<count($item); $i++) {
                        $role = array_values($item)[$i];
                        if($role->name == User::ROLE_DEALER_ASSOCIATE) {
                            $du_auth->revoke($role,$duId);
                        }
                    }
                    //soft delete
                    $du->status = MyCustomActiveRecord::STATUS_DISABLED;
                    $du->save();
                    $success = true;
                }   
            }catch (yii\db\IntegrityException $e) {
                    $transaction->rollback();
                    Yii::error($e->getMessage(), 'DealerUserForm Delete Staff');
                 }

            if($success) {
                $transaction->commit();
                $dm = DealerUser::getAllDealerManager($dealer->id);
                for ($i=0; $i < count($dm) ; $i++) {
                    $fcm = new FcmDealerDeleteStaff($dm[$i], $dh, User::ROLE_DEALER_MANAGER);
                    $fcm->send();
                }
                //notify associate when manager remove him 
                $fcm = new FcmDealerDeleteStaff($du, $dh, User::ROLE_DEALER_ASSOCIATE);
                $fcm->send();

                return true;

            } else {
                $transaction->rollback();
                $this->addError("", Yii::t('common', 'Unable to delete staff.'), CustomHttpException::UNPROCESSABLE_ENTITY);
                return null;
            } 

        }else{
            $this->addError('dealer_staff_id', Yii::t('common', 'Staff does not exist.'), CustomHttpException::BAD_REQUEST);
            return null;
        }
        
    }


}
