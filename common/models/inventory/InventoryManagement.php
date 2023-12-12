<?php
namespace common\models\inventory;

use Yii;
use yii\base\Model;
use common\components\MyCustomModel;
use common\models\User;
use common\models\InstapPlan;
use common\models\DealerOrderInventoryOverview;
use common\models\DealerCompany;
use common\models\DealerInventoryAllocationHistory;
use common\models\DealerCompanyDealer;
use common\models\DealerOrderInventory;
use api\components\CustomHttpException;
use common\components\MyCustomActiveRecord;

class InventoryManagement extends MyCustomModel
{
    public $company_id;
    public $plan_id;
    public $company_downline_id;
    public $assign_amount;
    public $amount;


    public static function countAllDownlineInventory($company_id, $plan_id) {
        $total = 0;
        $downline_arr = DealerCompanyDealer::getDownlineArray($company_id);
        if($downline_arr) {
            foreach($downline_arr as $downline) {
                $inv = DealerOrderInventoryOverview::find()->Where(['dealer_company_id'=> $downline['dealer_company_downline_id']])->andWhere(['plan_id' => $plan_id])->one();
                if($inv) {
                    $total = $total + $inv->quota;
                }
            }
        }

        return $total;
    }

    public static function countDownlineAllocatedStock($company_id, $plan_id) {
        $total = 0;
        $downline_arr = DealerCompanyDealer::getDownlineArray($company_id);
        if($downline_arr) {
            foreach($downline_arr as $downline) {
                $inventories = DealerInventoryAllocationHistory::find()->Where(['from_company_id'=> $company_id])->andWhere(['plan_id' => $plan_id])->andWhere(['action' => DealerInventoryAllocationHistory::ACTION_ALLOCATE])->andWhere(['to_company_id' => $downline['dealer_company_downline_id']])->all();
                if($inventories) {
                    foreach($inventories as $inventory) {
                        $total = $total + $inventory->amount;
                    }
                }
            }
        }

        return $total;
    }

    public static function getRemainingStock($plan_id, $dealer_company_id) {
        $inv = DealerOrderInventory::find()->andWhere(['plan_id' => $plan_id])->andWhere(['dealer_company_id' => $dealer_company_id])->andWhere(['plan_pool_id' => null])->andWhere(['or', ['activation_token' => null], ['<','expire_at', time()]])->andWhere(['status' => MyCustomActiveRecord::STATUS_ENABLED])->all();
        if($inv) {
            $inv_pool = count($inv);
        }else {
             $inv_pool = 0;
        }
        return $inv_pool;
    }

    public static function getActivatedStock($plan_id, $dealer_company_id) {
        $inv = DealerOrderInventory::find()->andWhere(['plan_id' => $plan_id])->andWhere(['dealer_company_id' => $dealer_company_id])->all();
        if($inv) {
            $inv_pool = count($inv);
        }else {
             $inv_pool = 0;
        }
        return $inv_pool;
    }


}
