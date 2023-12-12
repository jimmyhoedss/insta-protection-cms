<?php

namespace common\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\DealerOrderInventoryOverview;

/**
 * DealerOrderInventoryOverviewSearch represents the model behind the search form of `common\models\DealerOrderInventoryOverview`.
 */
class DealerOrderInventoryOverviewSearch extends DealerOrderInventoryOverview
{
    /**
     * {@inheritdoc}
     */
    public $company_name;

    public function rules()
    {
        return [
            [['id', 'dealer_company_id', 'plan_id', 'quota', 'overall', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['status','company_name'], 'safe'],
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

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = DealerOrderInventoryOverview::find()->innerJoinWith('dealer', true);
        // $query = DealerOrderInventoryOverview::find()->innerJoinWith('dealer', true)->addGroupBy('dealer_company_id');
        $query->andWhere(["dealer_company.region_id"=>Yii::$app->session->get('region_id')]);
        // print_r($query->createCommand()->getRawSql());
        // exit();
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
            'dealer_company_id' => $this->dealer_company_id,
            'plan_id' => $this->plan_id,
            'quota' => $this->quota,
            'overall' => $this->overall,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'status', $this->status])
              ->andFilterWhere(['like', 'business_name', $this->company_name]);

        return $dataProvider;
    }
}
