<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\View;
use yii\filters\AccessControl;
use common\models\User;
use common\models\InstapPlanPool;
use common\models\InstapPlan;
use common\models\UserCase;
use common\models\DealerOrder;
use common\models\UserPlanAction;
use common\models\form\DashboardForm;
use common\models\search\TimelineEventSearch;
use common\components\Utility;
use common\models\Elasticsearch;

class DashboardController extends Controller
{
    public $layout = 'common';

    public function behaviors()
    {
        return [
           
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                       //'actions' => ['index'],
                       'allow' => true,
                       // 'roles' => [User::ROLE_ADMINISTRATOR],
                       'roles' => ['@'],
                    ],
                ],
            ],
            /**/
        ];
    }
    public function actionIndex()
    {
        $model = new DashboardForm();
        //trigger for first load
        //toDo replace unixtime with strtotime heh
        $first_day_of_year = strtotime('first day of January 00:00:00 this year');
        $last_day_of_year = strtotime("last day of December 23:59:59 this year"); //first day of last year

        $PlanData = DashboardForm::countPlanTimeSeries($first_day_of_year, $last_day_of_year);
        $claimData = DashboardForm::countClaimTimeSeries($first_day_of_year, $last_day_of_year);
        if ($model->load(Yii::$app->request->post())) {
            $start = strtotime($model->date_start);
            $end = strtotime($model->date_end);
            $PlanData = DashboardForm::countPlanTimeSeries($start, $end);
            $claimData = DashboardForm::countClaimTimeSeries($start, $end);

        }
        $year_start = strtotime("first day of January 00:00:00 last year"); //first day of last year 
        $year_end = strtotime("last day of December 23:59:59 last year"); //first day of last year

        $last_2_month_start = strtotime("first day of this month 00:00:00 -2 months"); //last 3 months 1st day of month
        $last_2_month_end = strtotime("last day of this month 23:59:59 -2 months");  //last 3 months laast day of month

        $last_month_start = strtotime("first day of this month 00:00:00 -1 months"); //last 3 months 1st day of month
        $last_month_end = strtotime("last day of this month 23:59:59 -1 months");  //last 3 months laast day of 

        $this_month_start = strtotime("first day of this month 00:00:00"); //last 3 months 1st day of month
        $this_month_end = strtotime("last day of this month 23:59:59");  //last 3 months laast day of 

        //find number of register plan
        $plans = InstapPlan::find()->select(['id', 'name'])->andWhere(['region_id' => Yii::$app->session->get("region_id")])->asArray()->all();
        $grossSales = DashboardForm::grossSales();
        // $PlanData = DashboardForm::countPlanTimeSeries3();
        // $claimData = DashboardForm::countClaimTimeSeries3();

        //register variable to js file
        $this->getView()->registerJsVar("grossSales", $grossSales, View::POS_BEGIN);
        $this->getView()->registerJsVar("planData", $PlanData, View::POS_BEGIN);
        $this->getView()->registerJsVar("claimData", $claimData, View::POS_BEGIN);




        return $this->render('index', [
            'model' => $model,
            'year_start' => $year_start,
            'year_end' => $year_end,
            'last_2_month_start' => $last_2_month_start,
            'last_2_month_end' => $last_2_month_end,
            'last_month_start' => $last_month_start,
            'last_month_end' => $last_month_end,
            'this_month_start' => $this_month_start,
            'this_month_end' => $this_month_end,
            // 'page' => $date_time_category,
            'all_plans' => $plans
        ]);
    }
// call by ajax
    public function actionPlanInfo()
    {
        $plan_id = Yii::$app->request->post('plan_id');
        $data = [];

        $approved_plan_id = UserPlanAction::find()
            ->select(['plan_pool_id', 'created_at'])
            ->andWhere(['action_status' => UserPlanAction::ACTION_APPROVE])
            ->orderBy(['created_at' => SORT_DESC])
            ->distinct()->all();

        $plan_pool_id_arr = array_column($approved_plan_id, 'plan_pool_id');
        //calculate 
        $totalApprovedPlan = InstapPlanPool::find()
            ->andWhere(['in','id', $plan_pool_id_arr])
            ->andWhere(['region_id' => Yii::$app->session->get("region_id"), 'plan_id' => $plan_id])
            ->count();

        $totalRegister = InstapPlanPool::find()->andWhere(['plan_status' => InstapPlanPool::STATUS_PENDING_REGISTRATION, 'region_id' => Yii::$app->session->get('region_id'), 'plan_id' => $plan_id])->count();

        $totalPendingApproval = InstapPlanPool::find()->andWhere(['plan_status' => InstapPlanPool::STATUS_PENDING_APPROVAL, 'region_id' => Yii::$app->session->get('region_id'), 'plan_id' => $plan_id])->count();

        $totalClarification = InstapPlanPool::find()->andWhere(['plan_status' => InstapPlanPool::STATUS_REQUIRE_CLARIFICATION, 'region_id' => Yii::$app->session->get('region_id'), 'plan_id' => $plan_id])->count();
        $totalCancelledPlan = InstapPlanPool::find()->andWhere(['plan_status' => InstapPlanPool::STATUS_CANCEL, 'region_id' => Yii::$app->session->get('region_id'), 'plan_id' => $plan_id])->count();
        // print_r($totalApprovedPlan);exit();  

        if (isset($plan_id)) {
            // $test = "Ajax Worked!";
            $o = (object) [];
            $o->number_of_register = $totalRegister;
            $o->number_of_approved = $totalApprovedPlan;
            $o->number_of_cancelled = $totalCancelledPlan;
            $o->number_of_pending_approval = $totalPendingApproval;
            $o->number_of_seeking_clarification = $totalClarification;
            $data[] = $o;
            $d = $data;

        } else {
            $d = "Ajax failed";
        }
        // \Yii::$app->response->format = Response::FORMAT_JSON;

        return \yii\helpers\Json::encode($d);
    }
//call by ajax
    public function actionClaimInfo()
    {
        $plan_id = Yii::$app->request->post('plan_id');
        // $plan_id = 1;
        // print_r($plan_id);exit();
        $data = [];
        //calculate 
        $plan_sold = (float)InstapPlanPool::find()->where(['plan_id'=>$plan_id])->andWhere(['not', ['plan_status'=> InstapPlanPool::STATUS_CANCEL]])->count();

        $totalApprovedClaim = UserCase::find()->joinWith('planPool', true)
            ->andWhere(['instap_plan_pool.region_id' => Yii::$app->session->get('region_id'),  'instap_plan_pool.plan_id' => $plan_id])
            ->andWhere(['in', 'user_case.current_case_status', UserCase::statusNotReject()])
            ->count();

        $totalRegister = UserCase::find()->joinWith('planPool', true)
            ->andWhere(['in', 'user_case.current_case_status', UserCase::statusApproved()])
            ->andWhere(['instap_plan_pool.region_id' => Yii::$app->session->get('region_id'), 'instap_plan_pool.plan_id' => $plan_id])
            ->count();

        $totalPendingApproval = UserCase::find()->joinWith('planPool', true)
            ->andWhere(['user_case.current_case_status' => UserCase::CASE_STATUS_CLAIM_PENDING, 'instap_plan_pool.region_id' => Yii::$app->session->get('region_id'), 'instap_plan_pool.plan_id' => $plan_id])
            ->count();

        $totalClarification = UserCase::find()->joinWith('planPool', true)
            ->andWhere(['user_case.current_case_status' => UserCase::CASE_STATUS_CLAIM_REQUIRE_CLARIFICATION, 'instap_plan_pool.region_id' => Yii::$app->session->get('region_id'), 'instap_plan_pool.plan_id' => $plan_id])
            ->count();

        $claim_ratio = $plan_sold>0?number_format((float)(InstapPlanPool::find()->where(['plan_id'=>$plan_id])->andWhere(['plan_status'=>InstapPlanPool::STATUS_COMPLETE_CLAIM])->count()/$plan_sold), 2, '.', ''):0;

        if (isset($plan_id)) {
            // $test = "Ajax Worked!";
            $o = (object) [];
            $o->number_of_register = $totalRegister;
            $o->number_of_approved = $totalApprovedClaim;
            $o->number_of_pending_approval = $totalPendingApproval;
            $o->number_of_seeking_clarification = $totalClarification;
            $o->claim_ratio = $claim_ratio;
            $data[] = $o;
            $d = $data;

        } else {
            $d = "Ajax failed";
        }
        return \yii\helpers\Json::encode($d);
    }
//call by ajax
    public function actionRevenueInfo()
    {
        $data = [];
        $plan_id = Yii::$app->request->post('plan_id');
        $symbol = InstapPlan::currencySymbol()[Yii::$app->session->get('region_id')];
        $plan = InstapPlan::find()->andWhere(['region_id' => Yii::$app->session->get('region_id'), 'id' => $plan_id])->one();

        $plan_sold = InstapPlanPool::find()->where(['plan_id'=>$plan_id])->andWhere(['not', ['plan_status'=> InstapPlanPool::STATUS_CANCEL]])->count();
        $plan_not_reject_or_cancel = InstapPlanPool::find()->andWhere(['region_id' => Yii::$app->session->get('region_id'),  'plan_id' => $plan_id])->andWhere(['not in', 'plan_status', [InstapPlanPool::STATUS_CANCEL, InstapPlanPool::STATUS_REJECT]])->count();

        if (isset($plan_id)) {
            $o = (object) [];
            $o->total_premium = $symbol.number_format((float)($plan->premium_price * $plan_not_reject_or_cancel), 2, '.', '');
            $o->total_retail = $symbol.number_format((float)($plan->retail_price*$plan_sold), 2, '.', '');
            $o->total_dealer = $symbol.number_format((float)($plan->dealer_price*$plan_sold), 2, '.', '');
            $o->total_revenue = $symbol.number_format((float)(($plan->dealer_price*$plan_sold) - ($plan->premium_price*$plan_sold)), 2, '.', '');

            $data[] = $o;
            $d = $data;

        } else {
            $d = "Ajax failed";
        }

        return \yii\helpers\Json::encode($d);
    }

    public function actionStatistics(){
        return $this->render('statistics');
    }

    public function actionKbn(){
        $model = new DashboardForm();
        if ($model->load(Yii::$app->request->post())) {
            //process time format
            $this->getView()->registerJsVar("date_start", $model->date_start, View::POS_BEGIN);
            $this->getView()->registerJsVar("date_end", $model->date_end, View::POS_BEGIN);
            // FROM_UNIXTIME(a.created_at, '%Y-%m-%dT%TZ')  
            print_r($model->date_start);
            print_r("<br>");
            print_r($model->date_end);exit();
            // print_r("expression");exit();
        }
        return $this->render('kbn', [
            'model' => $model
        ]);
    }


    public function actionTime($time) {
        $session = Yii::$app->session;
        $session->set('date_time_category', $time);
        return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
    }

     public function actionEls() {
        $param = '
            {
                "query": {
                    "bool" : {
                      "must": [
                         {
                           "range": {
                              "created_at": {
                                  "gte": "2020-09-06T11:14:58Z",
                                  "lte": "2021-04-06T11:16:13Z"
                              }
                            }
                         },
                        {
                          "match": {
                            "plan_pool_plan_status": "active"
                          }
                        }
                      ]
                    }
                }
            }';

        $ch = curl_init();
        curl_setopt( $ch,CURLOPT_URL, 'http://127.0.0.1:9200/dealer_order/_search' );
        curl_setopt( $ch,CURLOPT_POST, true );
        curl_setopt( $ch,CURLOPT_HTTPHEADER, array (
            'Content-Type: application/json'
        ));
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch,CURLOPT_POSTFIELDS, $param);
        $res = curl_exec($ch);
        curl_close( $ch );
        // $json = iterator_to_array($res);
        $json = json_decode($res, true);
        // return $res;
        print_r($json);
        print_r("\n");

    }

}
