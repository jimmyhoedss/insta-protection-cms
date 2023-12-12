<?php

namespace common\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\UserPlanDetailEdit;

/**
 * UserPlanDetailEditSearch represents the model behind the search form of `common\models\UserPlanDetailEdit`.
 */
class UserPlanDetailEditSearch extends UserPlanDetailEdit
{
    /**
     * {@inheritdoc}
     */
    public $full_name;
    
    public function rules()
    {
        return [
            [['id', 'plan_pool_id', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['sp_brand', 'sp_model_number', 'sp_model_name', 'sp_serial', 'sp_imei', 'sp_color', 'sp_dealer_code', 'sp_country_of_purchase', 'sp_device_purchase_date', 'sp_device_purchase_price', 'sp_device_capacity', 'notes'], 'safe'],
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
        $query = UserPlanDetailEdit::find()->innerJoinWith('planPool', true);
        $query->andWhere(["instap_plan_pool.region_id"=>Yii::$app->session->get('region_id')]);
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
            'plan_pool_id' => $this->plan_pool_id,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'sp_brand', $this->sp_brand])
            ->andFilterWhere(['like', 'sp_model_number', $this->sp_model_number])
            ->andFilterWhere(['like', 'sp_model_name', $this->sp_model_name])
            ->andFilterWhere(['like', 'sp_serial', $this->sp_serial])
            ->andFilterWhere(['like', 'sp_imei', $this->sp_imei])
            ->andFilterWhere(['like', 'sp_color', $this->sp_color])
            ->andFilterWhere(['like', 'sp_dealer_code', $this->sp_dealer_code])
            ->andFilterWhere(['like', 'sp_country_of_purchase', $this->sp_country_of_purchase])
            ->andFilterWhere(['like', 'sp_device_purchase_date', $this->sp_device_purchase_date])
            ->andFilterWhere(['like', 'sp_device_purchase_price', $this->sp_device_purchase_price])
            ->andFilterWhere(['like', 'sp_device_capacity', $this->sp_device_capacity])
            ->andFilterWhere(['like', 'notes', $this->notes]);

        return $dataProvider;
    }
}
