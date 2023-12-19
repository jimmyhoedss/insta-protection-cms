<?php

namespace common\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\UserCase;

/**
 * UserCaseSearch represents the model behind the search form about `common\models\UserCase`.
 */
class UserCaseSearch extends UserCase
{
    /**
     * @inheritdoc
     */
    public $claimReject = false;
    public $claimActive = false;
    public $claimPending = false;
    public $full_name;

    public function rules()
    {
        return [
            [['id', 'plan_pool_id', 'user_id', 'created_by', 'updated_by'], 'integer'],
            [['case_type', 'description', 'current_case_status', 'notes', 'status','full_name', 'category'], 'safe'],
            [['created_at', 'updated_at'], 'date', 'format'=>'dd-MM-yyyy', 'message'=>'{attribute} must be DD/MM/YYYY format.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    public function setClaimReject()
    {
        $this->claimReject = true;
    }

    public function setClaimActive()
    {
        $this->claimActive = true;
    }

    public function setClaimPending()
    {
        $this->claimPending = true;
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
        $query = UserCase::find()->innerJoinWith('planPool', true);
        $query->join('LEFT JOIN','instap_plan','instap_plan_pool.plan_id = instap_plan.id');
        $query->andWhere(["instap_plan.region_id"=>Yii::$app->session->get('region_id')]);
        $query->innerJoinWith('userProfile', true);

        if ($this->claimActive) {
          $query->andWhere(['not in', 'user_case.current_case_status', [UserCase::CASE_STATUS_CLAIM_CANCELLED, UserCase::CASE_STATUS_CLAIM_REJECTED]]);
        }

        if ($this->claimReject) {
          $query->andWhere(['or', ['user_case.current_case_status' => UserCase::CASE_STATUS_CLAIM_CANCELLED], ['user_case.current_case_status' => UserCase::CASE_STATUS_CLAIM_REJECTED]]);
        }

        if ($this->claimPending) {
          $query->andWhere(['user_case.current_case_status' => UserCase::CASE_STATUS_CLAIM_PENDING]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
            'defaultOrder' => [
                  'created_at' => SORT_DESC,
                ]
            ],
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'user_case.id' => $this->id,
            'plan_pool_id' => $this->plan_pool_id,
            'user_id' => $this->user_id,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'user_case.category' => $this->category,
        ]);

        $query->andFilterWhere(['like', 'case_type', $this->case_type])
            ->andFilterWhere(['like', 'user_case.description', $this->description])
            ->andFilterWhere(['like', 'current_case_status', $this->current_case_status])
            ->andFilterWhere(['like', 'notes', $this->notes])
            ->andFilterWhere(['like', 'status', $this->status])
            ->andFilterWhere(['like', 'concat(first_name, " " , last_name) ', $this->full_name])
            ->andFilterWhere(['like', "(date_format(FROM_UNIXTIME(user_case.created_at), '%d-%m-%Y %h:%i:%s %p' ))", $this->created_at])
              ->andFilterWhere(['like', "(date_format(FROM_UNIXTIME(user_case.updated_at), '%d-%m-%Y %h:%i:%s %p' ))", $this->updated_at]);


        return $dataProvider;
    }
}
