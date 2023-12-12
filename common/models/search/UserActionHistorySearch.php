<?php

namespace common\models\search;

use Yii;
use cheatsheet\Time;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\UserActionHistory;

/**
 * UserActionHistorySearch represents the model behind the search form of `common\models\UserActionHistory`.
 */
class UserActionHistorySearch extends UserActionHistory
{
    public $user_id = null;
    public $action = null;
    public $start_date = null;
    public $end_date = null;

    public function rules()
    {
        return [
            [['credit'], 'safe'],
            [['id', 'user_id', 'created_at'], 'integer'],
            [['type', 'action', 'parameter', 'latlng', 'device_id', 'device_type', 'app_version'], 'safe'],
            [['latitude', 'longitude'], 'number'],
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

    public function setSearchUser($id) {
        $this->user_id = $id;
    }

    public function setSearchAction($action){
        $this->action = $action;
    }

    public function setStartDate($d){
        $this->start_date = $d;
    }

    public function setEndDate($d){
        $this->end_date = $d;
    }

    public function search($params)
    {
        $query = UserActionHistory::find();

        // add conditions that should always apply here
        
        if ($this->user_id) {
            $query->andWhere(['user_id' => $this->user_id]);
        }

        if ($this->action) {
            $query->andWhere(['action' => $this->action]);
        }

        if ($this->start_date) {
            $query->andWhere(['>=', 'created_at', $this->start_date]);
        }
        
        if ($this->end_date) {
            $query->andWhere(['<=', 'created_at', ($this->end_date + Time::SECONDS_IN_A_DAY)]);
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
            //'user_id' => $this->user_id,
            'action' => $this->action,
            //'credit' => $this->credit,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            //'created_at' => $this->created_at,
        ]);

        $query->andFilterWhere(['like', 'type', $this->type])
            ->andFilterWhere(['like', 'credit', $this->credit])
            ->andFilterWhere(['like', 'device_type', $this->device_type])
            ->andFilterWhere(['like', 'parameter', $this->parameter])
            ->andFilterWhere(['like', 'app_version', $this->app_version])
            ->andFilterWhere(['like', 'latlng', $this->latlng])
            ->andFilterWhere(['like', 'device_id', $this->device_id])
            ->andFilterWhere(['like', 'device_type', $this->device_type]);

        return $dataProvider;
    }
}
