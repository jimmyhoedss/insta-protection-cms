<?php

namespace common\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\DealerCompany;
use common\components\MyCustomActiveRecord;

/**
 * DealerCompanySearch represents the model behind the search form of `common\models\DealerCompany`.
 */
class DealerCompanySearch extends DealerCompany
{
    public $order_mode;
    public $pageSize = 20;

    public function rules()
    {
        return [
            [['id', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['region_id', 'business_registration_number', 'business_name', 'business_address', 'business_zip_code', 'business_phone', 'business_email', 'notes','sp_inventory_order_mode','business_contact_person', 'order_type'], 'safe'],
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

    public function search($params)
    {
        $query = DealerCompany::find();

        $query->andWhere(["region_id"=>Yii::$app->session->get('region_id'), 'status' => MyCustomActiveRecord::STATUS_ENABLED]);
        if($this->order_mode === DealerCompany::INVENTORY_MODE_STOCKPILE) {
            $query->andWhere(['sp_inventory_order_mode' => $this->order_mode]);
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
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'region_id', $this->region_id])
            ->andFilterWhere(['like', 'business_registration_number', $this->business_registration_number])
            ->andFilterWhere(['like', 'business_name', $this->business_name])
            ->andFilterWhere(['like', 'business_address', $this->business_address])
            ->andFilterWhere(['like', 'business_zip_code', $this->business_zip_code])
            ->andFilterWhere(['like', 'business_phone', $this->business_phone])
            ->andFilterWhere(['like', 'business_email', $this->business_email])
            ->andFilterWhere(['like', 'business_contact_person', $this->business_contact_person])
            ->andFilterWhere(['like', 'sp_inventory_order_mode', $this->sp_inventory_order_mode])
            ->andFilterWhere(['like', 'notes', $this->notes]);

        return $dataProvider;
    }
}
