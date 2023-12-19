<?php

namespace common\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\InstapPlanPool;

/**
 * InstapPlanPoolSearch represents the model behind the search form of `common\models\InstapPlanPool`.
 */
class InstapPlanPoolSearch extends InstapPlanPool
{
    public $pendingApproval = false;
    public $full_name;
    public $company_name;
    public $coverage_period;
    public $plan_type;
    public $planController = false;
    public $user_id;
    public $declarationReport = false;
    public $claim_plans = false;
    public $plan_pool_id_arr;
    //set pageSize to false when need to generate report
    public $pageSize = 20;


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'plan_id', 'user_id','coverage_start_at', 'coverage_end_at', 'created_by','ew_coverage_start_at', 'ew_coverage_end_at', 'updated_by'], 'integer'],
            [['region_id', 'plan_category', 'plan_sku', 'policy_number', 'plan_status', 'notes', 'status','full_name', 'plan_type', 'company_name', 'dealer_company_id'], 'safe'],
            [['created_at', 'updated_at', 'coverage_period', 'ew_coverage_period'], 'date', 'format'=>'dd-MM-yyyy', 'message'=>'{attribute} must be DD/MM/YYYY format.'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    public function setPlanController()
    {
        $this->planController = true;
    }

    public function setPendingApproval()
    {
        $this->pendingApproval = true;
    }

    public function setUserId($id)
    {
        $this->user_id = $id;
    }

    public function setDeclarationReportId($plan_pool_id_arr)
    {
        $this->declarationReport = true;
        $this->plan_pool_id_arr = $plan_pool_id_arr;
    }
    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {

        $query = InstapPlanPool::find()->innerJoinWith('plan', true);
        $query->andWhere(["instap_plan.region_id"=>Yii::$app->session->get('region_id')]);
        if ($this->user_id){
          $query->andWhere(['user_id' => $this->user_id]);
        }
        if ($this->planController) {
          $query->innerJoinWith('userProfile', true)->innerJoinWith('dealer', true);
        }
        // add conditions that should always apply here
// print_r($this->company_name);exit();
        if ($this->pendingApproval) {
          $query->andWhere(['instap_plan_pool.plan_status' => InstapPlanPool::STATUS_PENDING_APPROVAL]);
        }

        if($this->declarationReport) {
          //search all plan with action approved and ignore the cancelled plan
            $query->andWhere(['in', 'instap_plan_pool.id', $this->plan_pool_id_arr])->andWhere(['<>', 'instap_plan_pool.plan_status', InstapPlanPool::STATUS_CANCEL]);
            $this->pageSize = false; //ohNote: set to false to display all the data for report
        }

        if($this->claim_plans) {
            $query->andWhere(['in', 'instap_plan_pool.plan_status', [InstapPlanPool::STATUS_ACTIVE, InstapPlanPool::STATUS_PENDING_CLAIM, InstapPlanPool::STATUS_COMPLETE_CLAIM]]);
        }

        // if ($this->user_id != null) {
        //     $query->andWhere(['user_id'=>$this->user_id]);
        // }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
            'defaultOrder' => [
                  'created_at' => SORT_DESC,
                ]
            ],
            'pagination' => [
                  'pageSize' => $this->pageSize,
              ],
        ]);

        if ($this->planController) {
          $dataProvider->sort->attributes['full_name'] = [
              'asc' => ['user_profile.first_name' => SORT_ASC , 'user_profile.last_name' => SORT_ASC],
              'desc' => ['user_profile.first_name' => SORT_DESC , 'user_profile.last_name' => SORT_ASC],
          ];

        }

        $this->load($params);

        if (!$this->validate()) {
          // print_r($this->getErrors());exit();
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'plan_id' => $this->plan_id,
            'dealer_company_id' => $this->dealer_company_id,
            'user_id' => $this->user_id,
            // 'coverage_start_at' => $this->coverage_start_at,
            // 'coverage_end_at' => $this->coverage_end_at,
            // 'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            // 'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'region_id', $this->region_id])
              ->andFilterWhere(['like', 'plan_category', $this->plan_category])
              ->andFilterWhere(['like', 'plan_sku', $this->plan_sku])
              ->andFilterWhere(['like', 'policy_number', $this->policy_number])
              ->andFilterWhere(['like', 'plan_status', $this->plan_status])
              ->andFilterWhere(['like', 'notes', $this->notes])
              ->andFilterWhere(['like', 'status', $this->status])
              // ->andFilterWhere(['like', 'instap_plan.coverage_period', $this->coverage_period])
              ->andFilterWhere(['like', 'instap_plan.name', $this->plan_type])
              ->andFilterWhere(['like', "(date_format(FROM_UNIXTIME(instap_plan_pool.coverage_start_at), '%d-%m-%Y %h:%i:%s %p' ))", $this->coverage_period])
              ->andFilterWhere(['like', "(date_format(FROM_UNIXTIME(instap_plan_pool.ew_coverage_start_at), '%d-%m-%Y %h:%i:%s %p' ))", $this->ew_coverage_period])
              ->andFilterWhere(['like', "(date_format(FROM_UNIXTIME(instap_plan_pool.created_at), '%d-%m-%Y %h:%i:%s %p' ))", $this->created_at])
              ->andFilterWhere(['like', "(date_format(FROM_UNIXTIME(instap_plan_pool.updated_at), '%d-%m-%Y %h:%i:%s %p' ))", $this->updated_at]);
              //oh:filter by date

// print_r($query->createCommand()->getRawSql());exit();
        if ($this->planController) {
          $query->andFilterWhere(['or',
            ['like','user_profile.first_name',$this->full_name],
            ['like','user_profile.first_name',$this->full_name]])
          ->andFilterWhere(['like', 'dealer_company.business_name', $this->company_name]);
        }
              

        return $dataProvider;
    }
}
