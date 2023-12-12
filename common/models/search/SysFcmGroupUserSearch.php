<?php

namespace common\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\SysFcmGroup;
use common\models\SysFcmGroupUser;

/**
 * SysFcmGroupUserSearch represents the model behind the search form of `\common\models\SysFcmGroupUser`.
 */
class SysFcmGroupUserSearch extends SysFcmGroupUser
{
    public $fcm_group_id = null;
    public function rules()
    {
        return [
            [['id', 'fcm_group_id', 'user_id', 'created_at'], 'integer'],
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

    public function setSearchGroup($id) {
        $model = SysFcmGroup::find()->AndWhere(['id'=>$id])->one();
        $this->fcm_group_id = $model->id;
    }

    public function search($params)
    {
        $query = SysFcmGroupUser::find();
        if ($this->user_id) {
            $query->andWhere(['fcm_group_id' => $this->fcm_group_id]);
        }
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
            'fcm_group_id' => $this->fcm_group_id,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at,
        ]);

        return $dataProvider;
    }
}
