<?php

namespace common\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\User;

/**
 * UserSearch represents the model behind the search form of `common\models\User`.
 */
class UserSearch extends User
{
    public $inactiveMode = false;
    public $activeMode = false;
    public $disabledMode = false;
    public $suspicious = false;
    public $full_name;
    public $ipStaffMode = false;
    public $birthdayOfMonth = false; //get birthday of the month

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'created_by', 'updated_by', 'login_attempt'], 'integer'],
            [['created_at', 'updated_at', 'login_at'], 'date', 'format'=>'dd-MM-yyyy', 'message'=>'{attribute} must be DD/MM/YYYY format.'],
            [['region_id', 'mobile_calling_code', 'mobile_number', 'mobile_number_full', 'mobile_status', 'password_salt', 'password_hash', 'fcm_token', 'email', 'email_status', 'account_status', 'suspicious_flag', 'auth_key', 'access_token', 'notes', 'status','full_name'], 'safe'],
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
    public function searchDisableMode() {
        $this->disabledMode = true;
    }
    public function searchSuspiciousUser() {
        $this->suspicious = true;
    }
    public function searchIpStaffMode() {
        $this->ipStaffMode = true;
    }
    public function searchBirthdayOfMonth() {
        $this->birthdayOfMonth = true;
    }

    public function search($params)
    {
        //$query = User::find();
        $query = User::find()->innerJoinWith('userProfile', true);

        $query->andWhere(["region_id"=>Yii::$app->session->get('region_id')]);
        // add conditions that should always apply here
        if ($this->disabledMode) {
            $query->andWhere(['account_status' => User::ACCOUNT_STATUS_SUSPENDED]);
        }
        if ($this->ipStaffMode) {
            $query->join('LEFT JOIN','rbac_auth_assignment','rbac_auth_assignment.user_id = id')
            ->andFilterWhere(['or', ['rbac_auth_assignment.item_name' => [User::ROLE_IP_ADMINISTRATOR, User::ROLE_IP_MANAGER, User::ROLE_IP_ADMIN_ASSISTANT, User::ROLE_IP_SUPER_ADMINISTRATOR]]])->orderBy(['rbac_auth_assignment.created_at' => SORT_DESC]);
        }
        if($this->birthdayOfMonth) {
            $query->andWhere(["MONTH(STR_TO_DATE(birthday, '%d-%m-%Y'))" => date('m')]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
            'defaultOrder' => [
                  'created_at' => SORT_DESC
                ]
            ],
        ]);

        $dataProvider->sort->attributes['full_name'] = [
            'asc' => ['user_profile.first_name' => SORT_ASC , 'user_profile.last_name' => SORT_ASC],
            'desc' => ['user_profile.first_name' => SORT_DESC , 'user_profile.last_name' => SORT_ASC],
        ];

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            // 'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            // 'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
            // 'login_at' => $this->login_at,
            'login_attempt' => $this->login_attempt,
        ]);

        $query->andFilterWhere(['=', 'region_id', $this->region_id])
            ->andFilterWhere(['like', 'mobile_calling_code', $this->mobile_calling_code])
            ->andFilterWhere(['like', 'mobile_number', $this->mobile_number])
            ->andFilterWhere(['like', 'mobile_number_full', $this->mobile_number_full])
            ->andFilterWhere(['=', 'mobile_status', $this->mobile_status])
            ->andFilterWhere(['like', 'password_salt', $this->password_salt])
            ->andFilterWhere(['like', 'password_hash', $this->password_hash])
            ->andFilterWhere(['like', 'fcm_token', $this->fcm_token])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['=', 'email_status', $this->email_status])
            ->andFilterWhere(['=', 'account_status', $this->account_status])
            ->andFilterWhere(['like', 'suspicious_flag', $this->suspicious_flag])
            ->andFilterWhere(['like', 'auth_key', $this->auth_key])
            ->andFilterWhere(['like', 'access_token', $this->access_token])
            ->andFilterWhere(['like', 'notes', $this->notes])
            ->andFilterWhere(['like', 'status', $this->status])
            ->andFilterWhere(['like', 'concat(first_name, " " , last_name) ', $this->full_name])
            ->andFilterWhere(['like', "(date_format(FROM_UNIXTIME(user.created_at), '%d-%m-%Y %h:%i:%s %p' ))", $this->created_at])
            ->andFilterWhere(['like', "(date_format(FROM_UNIXTIME(user.login_at), '%d-%m-%Y %h:%i:%s %p' ))", $this->login_at]);

            // print_r($query->createCommand()->getRawSql());exit();
    
            // ->andFilterWhere(['like', 'user_profile.last_name', $this->full_name]);

            // print_r($this->full_name);exit();

        return $dataProvider;
    }
}
