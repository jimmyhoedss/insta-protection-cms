<?php

namespace common\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\QcdRetailStore;
use common\components\MyCustomActiveRecord;

class QcdRetailStoreSearch extends QcdRetailStore
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['is_service_hub', 'is_courier', 'device_maker_id', 'is_asp'], 'integer'],
            [['retail_store', 'country_code', 'state_code', 'state_name', 'city_name', 'address', 'opening_hours', 'email', 'telephone', 'state'], 'safe'],
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
        $query = QcdRetailStore::find();
        $query->andWhere(["country_code"=>Yii::$app->session->get('region_id'), "status" => MyCustomActiveRecord::STATUS_ENABLED]);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
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
            'is_service_hub' => $this->is_service_hub,
            'is_courier' => $this->is_courier,
            'device_maker_id' => $this->device_maker_id,
            'is_asp' => $this->is_asp,
        ]);

        $query->andFilterWhere(['like', 'retail_store', $this->retail_store])
            ->andFilterWhere(['like', 'country_code', $this->country_code])
            ->andFilterWhere(['like', 'state_code', $this->state_code])
            ->andFilterWhere(['like', 'state_name', $this->state_name])
            ->andFilterWhere(['like', 'city_name', $this->city_name])
            ->andFilterWhere(['like', 'address', $this->address])
            ->andFilterWhere(['like', 'opening_hours', $this->opening_hours])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'telephone', $this->telephone])
            ->andFilterWhere(['like', 'state', $this->state]);

        return $dataProvider;
    }
}
