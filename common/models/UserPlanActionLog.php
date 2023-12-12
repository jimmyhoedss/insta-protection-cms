<?php

namespace common\models;

use Yii;
use common\models\UserPlanAction;

/**
 * This is the model class for table "user_plan_action_log".
 *
 * @property int $id
 * @property int $plan_pool_id
 * @property string $action_status
 * @property string|null $status
 * @property int|null $created_at
 * @property int|null $created_by
 * @property int|null $updated_at
 * @property int|null $updated_by
 */
class UserPlanActionLog extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_plan_action_log';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['plan_pool_id'], 'required'],
            [['plan_pool_id', 'plan_id', 'dealer_company_id', 'created_at'], 'integer'],
            [['action_status', 'status', 'region_id', 'plan_category', 'plan_name', 'plan_tier'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'plan_pool_id' => 'Plan Pool ID',
            'action_status' => 'Action Status',
            'status' => 'Status',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * {@inheritdoc}
     * @return \common\models\query\UserPlanActionLogQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\UserPlanActionLogQuery(get_called_class());
    }

    public static function makeModel($planPool, $planAction) {
        $m = new SELF();
        $instap_plan = InstapPlan::find()->where(['id' => $planPool->plan_id])->asArray()->one();
        $m->plan_pool_id = $planPool->id;
        $m->plan_id = $planPool->plan_id;
        $m->plan_name = $instap_plan['name'];
        $m->plan_tier = $instap_plan['tier'];
        $m->dealer_company_id = $planPool->dealer_company_id;
        $m->region_id = $planPool->region_id;
        $m->plan_category = $planPool->plan_category;
        $m->action_status = $planAction;
        $m->created_at = time();  

        return $m;
    }
    //use for console insert
    public static function makeModel2($planPool, $planAction) {
        $m = new SELF();
        $instap_plan = InstapPlan::find()->where(['id' => $planPool->plan_id])->asArray()->one();
        $m->plan_pool_id = $planPool->id;
        $m->plan_id = $planPool->plan_id;
        $m->plan_name = $instap_plan['name'];
        $m->plan_tier = $instap_plan['tier'];
        $m->dealer_company_id = $planPool->dealer_company_id;
        $m->region_id = $planPool->region_id;
        $m->plan_category = $planPool->plan_category;
        $m->action_status = $planAction->action_status;
        $m->created_at = $planAction->created_at; 
        if(!$planAction) {
            $m->action_status = UserPlanAction::ACTION_REGISTRATION;
            $m->created_at = $planPool->created_at;  
        } else {
            // if(!isset($planAction->action_status)) {echo $planPool->plan_sku; exit();}
            $m->action_status = $planAction->action_status;
            $m->created_at = $planAction->created_at;  
        }
        
        return $m;
    }
}
