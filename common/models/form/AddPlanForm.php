<?php
namespace common\models\form;

use Yii;
use yii\base\Model;
use common\models\DealerCompany;
use common\models\DealerOrder;
use common\models\DealerOrderAdHoc;
use common\models\DealerOrderInventory;
use common\models\InstapPlan;
use common\models\InstapPlanPool;
use common\models\User;
use common\models\UserPlan;
use common\models\SysSocketNotification;
use common\models\UserPlanActionLog;
use common\models\UserPlanAction;
use common\models\fcm\FcmPlanStatusChanged;


use common\components\MyCustomModel;
use common\components\Utility;
use api\components\CustomHttpException;

class AddPlanForm extends MyCustomModel
{
    public $channel;
    public $activation_token;

    public function rules() {
        return [
            [['channel', 'activation_token'], 'required'],
            ['channel', 'in', 'range' => array_keys(InstapPlan::allSalesChannel())]
            //[['activation_token'], 'string', 'min' => 32, 'max' => 64],
        ];
    }
    
    public function processOrder() {
        $order = null;
        $user = Yii::$app->user->identity;

        if ($this->channel == InstapPlan::SALES_CHANNEL_DEALER_TYPE1) {
            $order = DealerOrderAdHoc::find()->where(['activation_token'=>$this->activation_token])->one();
        } else if ($this->channel == InstapPlan::SALES_CHANNEL_DEALER_TYPE2) {
            $order = DealerOrderInventory::find()->andWhere(['activation_token'=>$this->activation_token])->andWhere(['plan_pool_id' => null])->one();
        }

        if (!$order) {
            $this->addError('activation_token', Yii::t('common', 'Cannot add plan, token is invalid or expired.'), CustomHttpException::KEY_INVALID_OR_EXPIRED_TOKEN);
            return null;
        } else if ($order->expire_at < time()){
            $this->addError('activation_token', Yii::t('common', "Order expired."));
            return null;
        } else if($order->plan->region_id != $user->region_id){
            $this->addError('activation_token', Yii::t('common', "Not allowed to purchase plans from other country."));
            return null;
        }

        return $this->saveToPool($order);
    }


    private function saveToPool($order){
        //DealerOrderAdHoc or DealerOrderInventory model
        $success = false;
        $user = Yii::$app->user->identity;

        $transaction = Yii::$app->db->beginTransaction();
        try {

            $plan = $order->plan;
            $dealer = DealerCompany::getDealerCompanyByUserId($order->dealer_user_id);          
            $pool = InstapPlanPool::makeModel($plan, $dealer, $user);

            
            $pool->save();
            $do = DealerOrder::makeModel($dealer, $order->dealer_user_id, $pool);
            $do->save(); 
            $up = UserPlan::makeModel($user, $pool);                
            $up->save();
            //save action log for dashboard graph plotting
            $actionlog = UserPlanActionLog::makeModel($pool, UserPlanAction::ACTION_REGISTRATION);
            $actionlog->save();

            //add hoc row will be delete
            if ($order instanceof DealerOrderAdHoc) {
                $order->delete();
            } else if ($order instanceof DealerOrderInventory) {
                $order->plan_pool_id = $pool->id;
                $order->dealer_user_id = $order->dealer_user_id;
                $order->save();
            }

            if ($pool->hasErrors() || $do->hasErrors() || $up->hasErrors() || $order->hasErrors() || $actionlog->hasErrors()) {
                //for logging to sys log
                $msg = print_r($pool->getErrors(),true) . print_r($do->getErrors(),true) . print_r($up->getErrors(),true) . print_r($order->getErrors(),true) . print_r($actionlog->getErrors(),true);
                throw new \Exception($msg);
            } else {
                $success = true;
            }
            

        } catch (yii\db\IntegrityException $e) {
            Yii::error($e->getMessage(), 'AddPlanForm');
        } catch ( \Exception $e ) {
            Yii::error($e->getMessage(), 'AddPlanForm');
        }
        
        if($success) {
            $transaction->commit();
            $socketNotication = SysSocketNotification::RESULT_SUCCESS;            
        } else {
            $transaction->rollback();                
            $socketNotication = SysSocketNotification::RESULT_FAIL;            
            $this->addError('activation_token', Yii::t('common', "Cannot add plan."));
            $pool = null;
        }

        $socket = SysSocketNotification::makeModel(SysSocketNotification::NOTIFY_SCAN_QR_PLAN_POOL, $order->dealer_user_id, $socketNotication);
        $socket->send();

        return $pool;        
    }



}
