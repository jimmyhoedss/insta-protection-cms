<?php

namespace common\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\UserCaseAction;

/**
 * UserCaseActionSearch represents the model behind the search form about `common\models\UserCaseAction`.
 */
class UserCaseActionSearch extends UserCaseAction
{
    
    public $case_id = null;

    public function rules()
    {
        return [
            [['id', 'case_id', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['description', 'notes', 'action_status', 'status'], 'safe'],
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

    public function setCaseId($id)
    {
        $this->case_id = $id;
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
        $query = UserCaseAction::find();

        if ($this->case_id != null) {
            $query->andWhere(['case_id'=>$this->case_id]);
        }

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
            'case_id' => $this->case_id,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'notes', $this->notes])
            ->andFilterWhere(['like', 'action_status', $this->action_status])
            ->andFilterWhere(['like', 'status', $this->status]);

        return $dataProvider;
    }
}
