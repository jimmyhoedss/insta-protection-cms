<?php

namespace common\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\DealerOrderHistory;

/**
 * DealerOrderHistorySearch represents the model behind the search form about `common\models\DealerOrderHistory`.
 */
class DealerOrderHistorySearch extends DealerOrderHistory
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'dealer_company_id', 'instap_plan_id', 'amount', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['notes', 'status'], 'safe'],
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

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = DealerOrderHistory::find();

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
            'id' => $this->id,
            'dealer_company_id' => $this->dealer_company_id,
            'instap_plan_id' => $this->instap_plan_id,
            'amount' => $this->amount,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'notes', $this->notes])
            ->andFilterWhere(['like', 'status', $this->status]);

        return $dataProvider;
    }
}
