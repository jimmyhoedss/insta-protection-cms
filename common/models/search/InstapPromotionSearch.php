<?php

namespace common\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\InstapPromotion;

/**
 * InstapPromotionSearch represents the model behind the search form of `common\models\InstapPromotion`.
 */
class InstapPromotionSearch extends InstapPromotion
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['created_at', 'updated_at'], 'date', 'format'=>'dd-MM-yyyy', 'message'=>'{attribute} must be DD/MM/YYYY format.'],
            [['title','region_id', 'description', 'thumbnail_base_url', 'thumbnail_path', 'status'], 'safe'],
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
        $query = InstapPromotion::find();

        $query->andWhere(["region_id"=>Yii::$app->session->get('region_id')]);
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
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
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'region_id', $this->region_id])
            ->andFilterWhere(['like', 'thumbnail_base_url', $this->thumbnail_base_url])
            ->andFilterWhere(['like', 'thumbnail_path', $this->thumbnail_path])
            ->andFilterWhere(['like', 'status', $this->status])
            ->andFilterWhere(['like', "(date_format(FROM_UNIXTIME(created_at), '%d-%m-%Y %h:%i:%s %p' ))", $this->created_at])
              ->andFilterWhere(['like', "(date_format(FROM_UNIXTIME(updated_at), '%d-%m-%Y %h:%i:%s %p' ))", $this->updated_at]);

        return $dataProvider;
    }
}
