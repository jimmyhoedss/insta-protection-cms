<?php

namespace common\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\DealerOrder;

/**
 * DealerOrderSearch represents the model behind the search form of `common\models\DealerOrder`.
 */
class DealerOrderSearch extends DealerOrder
{
    public $full_name;
    public $plan_pool_id_arr; 
    public $distributorReport = false;//boolean
    public $pageSize = 20;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'dealer_company_id', 'dealer_user_id', 'plan_pool_id', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['price'], 'number'],
            [['notes', 'status'], 'safe'],
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

    public function setDealerId($id)
    {
        $this->dealer_company_id = $id;
    }

    public function setDealerUserId($id)
    {
        $this->dealer_user_id = $id;
    }

    public function setDistributorActivationReportId($plan_pool_id_arr)
    {
        $this->distributorReport = true;
        $this->pageSize = false; //set pagination to false
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
        $query = DealerOrder::find();

        if($this->distributorReport) {
            $query->andWhere(['in', 'plan_pool_id', $this->plan_pool_id_arr]);
        }

        // add conditions that should always apply here

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

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'dealer_company_id' => $this->dealer_company_id,
            'dealer_user_id' => $this->dealer_user_id,
            'plan_pool_id' => $this->plan_pool_id,
            'price' => $this->price,
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
