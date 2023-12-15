<?php

namespace common\models;

use Yii;
use common\models\UserCaseAction;
class UserCaseActionLog extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_case_action_log';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['case_id', 'region_id', 'plan_id', 'plan_name', 'plan_tier', 'plan_category'], 'required'],
            [['case_id', 'plan_id', 'created_at'], 'integer'],
            [['action_status', 'status'], 'string'],
            [['region_id', 'plan_category'], 'string', 'max' => 4],
            [['plan_name'], 'string', 'max' => 255],
            [['plan_tier'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'case_id' => 'Case ID',
            'region_id' => 'Region ID',
            'plan_id' => 'Plan ID',
            'plan_name' => 'Plan Name',
            'plan_tier' => 'Plan Tier',
            'plan_category' => 'Plan Category',
            'action_status' => 'Action Status',
            'status' => 'Status',
            'created_at' => 'Created At',
        ];
    }

    /**
     * {@inheritdoc}
     * @return \common\models\query\UserCaseActionLogQuery the active query used by this AR class.
     */

     public static function makeModel($userCase, $caseAction) {
        $m = new SELF();
        $pool = $userCase->planPool;
        $instap_plan = InstapPlan::find()->where(['id' => $pool->plan_id])->asArray()->one();

        $m->case_id = $userCase->id;
        $m->plan_id = $instap_plan['id'];
        $m->plan_name = $instap_plan['name'];
        $m->plan_tier = $instap_plan['tier'];
        $m->region_id = $pool->region_id;
        $m->plan_category = $pool->plan_category;
        $m->action_status = $caseAction;
        $m->created_at = time();  
        // $m->created_at = $userCase->created_at;  
        
        return $m;
    }
    //toDo: delete after deployed
    public static function makeModel2($userCase, $caseAction) {
        $m = new SELF();
        $pool = $userCase->planPool;
        $instap_plan = InstapPlan::find()->where(['id' => $pool->plan_id])->asArray()->one();

        $m->case_id = $userCase->id;
        $m->plan_id = $instap_plan['id'];
        $m->plan_name = $instap_plan['name'];
        $m->plan_tier = $instap_plan['tier'];
        $m->region_id = $pool->region_id;
        $m->plan_category = $pool->plan_category;
        $m->action_status = $caseAction->action_status;
        $m->created_at = $caseAction->created_at;  
        
        return $m;
    }

    public static function find()
    {
        return new \common\models\query\UserCaseActionLogQuery(get_called_class());
    }
}
