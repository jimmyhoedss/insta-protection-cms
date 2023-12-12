<?php

namespace common\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\DealerUser;
use common\components\MyCustomActiveRecord;


/**
 * DealerUserSearch represents the model behind the search form of `common\models\DealerUser`.
 */
class DealerUserSearch extends DealerUser
{
    public $full_name;
    public $dealer;
    public $mobile_number;
    // public $planController = false;
    

    public function rules()
    {
        return [
            [['dealer_company_id', 'user_id', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['notes', 'full_name','dealer','mobile_number'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    // public function setDealerController()
    // {
    //     $this->planController = true;
    // }

    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    public function setDealerId($id) {
        $this->dealer_company_id = $id;
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
        $query = DealerUser::find()->innerJoinWith('userProfile', true);
        
        $query->innerJoinWith('dealer', true);
        $query->innerJoinWith('user', true);
        $query->andWhere(["dealer_company.region_id"=>Yii::$app->session->get('region_id'),'dealer_user.status' => MyCustomActiveRecord::STATUS_ENABLED]);

       

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
            // 'id' => $this->id,
            'dealer_company_id' => $this->dealer_company_id,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'notes', $this->notes])
        ->andFilterWhere(['like', 'first_name', $this->full_name])
        ->andFilterWhere(['like', 'business_name', $this->dealer])
        ->andFilterWhere(['like', 'mobile_number', $this->mobile_number])
        ->andFilterWhere(['like', 'concat(first_name, " " , last_name) ', $this->full_name]);


        return $dataProvider;
    }
}
