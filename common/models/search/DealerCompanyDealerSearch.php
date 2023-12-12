<?php

namespace common\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\DealerCompanyDealer;


class DealerCompanyDealerSearch extends DealerCompanyDealer
{
    public $company_name;
    public $company_name2;

    public function rules()
    {
        return [
            [['dealer_company_upline_id', 'dealer_company_downline_id'], 'integer'],
            [['company_name', 'company_name2'], 'safe'],
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
        $query = DealerCompanyDealer::find()->innerJoinWith('uplineComapny', true);
        $query->andWhere(["dealer_company.region_id"=>Yii::$app->session->get('region_id')]);
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
        // $query->andFilterWhere([
        //     'dealer_company_upline_id' => $this->dealer_company_upline_id,
        //     'dealer_company_downline_id' => $this->dealer_company_downline_id,
        // ]);

        $query->andFilterWhere(['like', 'dealer_company.business_name', $this->company_name])
        ->andFilterWhere(['like', 'dealer_company.business_name', $this->company_name2]);

        // print_r($query->createCommand()->getRawSql());exit();

        return $dataProvider;
    }
}
