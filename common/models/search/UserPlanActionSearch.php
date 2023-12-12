<?php

namespace common\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\UserPlanAction;
use common\models\UserCase;
use common\models\UserCaseAction;

/**
 * UserPlanActionSearch represents the model behind the search form about `common\models\UserPlanAction`.
 */
class UserPlanActionSearch extends UserPlanAction
{
    public $user_plan_pool_id = null;
    public $mergeCaseAction = false;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'plan_pool_id', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
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

    public function setPlanPoolId($id)
    {
        $this->user_plan_pool_id = $id;
    }

    public function setMergeCaseAction(){
        $this->mergeCaseAction = true;
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
        $query = UserPlanAction::find();

        if ($this->user_plan_pool_id != null) {
            $query->andWhere(['plan_pool_id'=>$this->user_plan_pool_id]);
        }

        if($this->mergeCaseAction){
            // $case = UserCase::find()->andWhere(['plan_pool_id'=>$this->user_plan_pool_id])->one();
            $case = UserCase::find()->where(['in', 'current_case_status', UserCase::statusNotReject()])->andWhere(['plan_pool_id'=>$this->user_plan_pool_id])->orderBy(['created_at'=>SORT_DESC])->limit(1)->one();
            
            if($case){
                $sql = "select * from user_plan_action where plan_pool_id = ".$this->user_plan_pool_id." union select * from user_case_action where case_id = ".$case->id." order by created_at DESC";
                $query = UserPlanAction::findBySql($sql);
            }
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
            'plan_pool_id' => $this->plan_pool_id,
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
