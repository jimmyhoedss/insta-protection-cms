<?php

namespace common\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\UserPlanDetailEditHistory;

/**
 * UserPlanDetailEditHistorySearch represents the model behind the search form of `common\models\UserPlanDetailEditHistory`.
 */
class UserPlanDetailEditHistorySearch extends UserPlanDetailEditHistory
{
    public $plan_pool_id;

    public function rules()
    {
        return [
            [['id', 'row_id', 'created_by', 'created_at'], 'integer'],
            [['model', 'controller', 'action', 'value', 'plan_pool_id'], 'safe'],
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

    public function setPlanPoolId($plan_pool_id)
    {
        $this->plan_pool_id = $plan_pool_id;
    }

    
    public function search($params)
    {
        $query = UserPlanDetailEditHistory::find();
        if ($this->plan_pool_id){
          $query->andWhere(['row_id' => $this->plan_pool_id]);
        }

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
            'defaultOrder' => [
                  'created_at' => SORT_DESC,
                ]
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'row_id' => $this->row_id,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
        ]);

        $query->andFilterWhere(['like', 'model', $this->model])
            ->andFilterWhere(['like', 'controller', $this->controller])
            ->andFilterWhere(['like', 'action', $this->action])
            ->andFilterWhere(['like', 'value', $this->value]);

        return $dataProvider;
    }
}
