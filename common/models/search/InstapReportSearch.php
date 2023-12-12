<?php

namespace common\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\InstapReport;

/**
 * InstapReportSearch represents the model behind the search form about `common\models\InstapReport`.
 */
class InstapReportSearch extends InstapReport
{
    public $report_type;

    public function rules()
    {
        return [
            [['id', 'date_start', 'date_end', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['type', 'document_base_url', 'document_path', 'report_status', 'region_id', 'status'], 'safe'],
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

    public function setDeclarationReport() {
        $this->report_type = InstapReport::TYPE_DECLARATION_REPORT;
    }

    public function setDistActivationReport() {
        $this->report_type = InstapReport::TYPE_DISTRIBUTOR_ACTIVATION_REPORT;
    }

    public function setAmTransactionReport(){
        $this->report_type = InstapReport::TYPE_AM_TRANSACTION_REPORT;
    }

    public function setRetailTransactionReport(){
        $this->report_type = InstapReport::TYPE_RETAIL_TRANSACTION_REPORT;
    }

    public function setSohReport(){
        $this->report_type = InstapReport::TYPE_SOH_REPORT;
    }

    public function setClaimSubmissionReport(){
        $this->report_type = InstapReport::TYPE_CLAIM_SUBMISSION_REPORT;
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
        $query = InstapReport::find()->andWhere(['region_id' => Yii::$app->session->get('region_id')]);

        if($this->report_type) {
            $query->andWhere(['type' => $this->report_type]);
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
            'date_start' => $this->date_start,
            'date_end' => $this->date_end,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'type', $this->type])
            ->andFilterWhere(['like', 'document_base_url', $this->document_base_url])
            ->andFilterWhere(['like', 'document_path', $this->document_path])
            ->andFilterWhere(['like', 'report_status', $this->report_status])
            ->andFilterWhere(['like', 'region_id', $this->region_id])
            ->andFilterWhere(['like', 'status', $this->status]);

        return $dataProvider;
    }
}
