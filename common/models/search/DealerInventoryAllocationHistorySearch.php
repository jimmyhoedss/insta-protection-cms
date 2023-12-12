<?php

namespace common\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\DealerInventoryAllocationHistory;


class DealerInventoryAllocationHistorySearch extends DealerInventoryAllocationHistory
{
    public $dealer_company_id;

    public function rules()
    {
        return [
            [['id', 'from_company_id', 'plan_id', 'amount', 'to_company_id', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['action'], 'safe'],
        ];
    }

    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    public function setCompanyId($id)
    {
        $this->dealer_company_id = $id;
    }

    public function search($params)
    {
        $query = DealerInventoryAllocationHistory::find();

        // add conditions that should always apply here
        if ($this->dealer_company_id) {
          $query->orWhere(['from_company_id'=> $this->dealer_company_id])->orWhere(['to_company_id'=> $this->dealer_company_id]);
        }

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
            'from_company_id' => $this->from_company_id,
            'plan_id' => $this->plan_id,
            'amount' => $this->amount,
            'to_company_id' => $this->to_company_id,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'action', $this->action]);

        return $dataProvider;
    }
}
