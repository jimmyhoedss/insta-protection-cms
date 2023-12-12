<?php
namespace common\models\form;

use yii\base\Model;
use Yii;
use yii\web\JsExpression;
use yii\helpers\Url;
use yii\helpers\Html;
use trntv\filekit\behaviors\UploadBehavior;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use common\models\InstapPlanPool;
use common\models\User;
use common\models\SysRegion;
use common\models\UserCase;
use common\models\InstapPlan;
use common\models\DealerOrder;
use common\models\DealerCompany;
use common\models\UserCaseAction;
use common\models\UserPlanAction;
use common\models\UserPlanActionLog;
use common\models\UserCaseActionLog;
use common\components\Utility;

class DashboardForm extends Model
{

    public $dateRange;
    public $date_start;
    public $date_end;
    public $plan_id;

    CONST MONTH_IN_YEAR = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    CONST DAY_IN_CURRENT_WEEK = ['monday this week', 'tuesday this week', 'wednesday this week', 'thursday this week', 'friday this week', 'saturday this week', 'sunday this week'];

    CONST THIS_WEEK = "this_week";
    CONST THIS_MONTH = "this_month";
    CONST THIS_YEAR = "this_year";


    public function init() {
        date_default_timezone_set("Asia/Singapore");
        // $this->months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        // print_r($this->months);exit();
        // $this->weeks = ['monday this week', 'tuesday this week', 'wednesday this week', 'thursday this week', 'friday this week', 'saturday this week', 'sunday this week'];


    }

    public function rules()
    {
        return [            
            [['date_start', 'date_end', 'plan_id'], 'integer'],      
            [['dateRange'], 'safe'],      
            // ['date_start', 'required'],
            // ['date_end', 'required'],
            // ['registration_photo', 'required', 'when' => function() {
            //     return ($this->type === self::REGISTRATION);
            // }]
        ];
    }
    
    public function attributeLabels()
    {
        return [
            'date_start' =>Yii::t('common', 'Date Start'),
            'date_end' =>Yii::t('common', 'Date End'),
        ];
    }

    public static function countTotalPlanRegistration($plan_id) 
    {
        $data = [];
        // print_r($this->months);exit();
        $date_time_category = Yii::$app->session->get("date_time_category");
        $period = self::getChartPeriodArr($date_time_category);
        for($i=0; $i<count($period); $i++) {
            $o = (object) [];
            $start = $period[$i][0];
            $end = $period[$i][1];
            $total = InstapPlanPool::find()->andWhere(['plan_status'=>InstapPlanPool::STATUS_PENDING_REGISTRATION])->andWhere(['region_id' => Yii::$app->session->get("region_id")])->andWhere(['between', 'created_at', $start, $end ])->andWhere(['plan_id' => $plan_id])->count();

            array_push($data, $total);
        }

        return $data;
    }

    public static function countPlanTimeSeries($start, $end)  
    {
        // $planCategoryArr = [ 'LSLT', 'LSSE', 'LSSP', 'LSTB'];
        // $planCategoryArr = [ 'LSLT', 'LSSE', 'LSSP', 'LSTB']; HL
        // $planCategoryArr = [ 'LSLT', 'LSSE', 'LSSP', 'LSTB']; AU
    
        // $data = [];
        // // print_r($this->months);exit();
        // $date_time_category = Yii::$app->session->get("date_time_category");
        // $period = self::getChartPeriodArr($date_time_category);
        // $start = 1578930112;//Monday, January 13, 2020 3:41:52 PM
        // $end = 1622312972; //May 29, 2021 06:29:32 PM
        $planCategoryArr = [ 'LSLT', 'LSSE', 'LSSP', 'LSTB'];

        $dataSetArr = self::getPlanByCategory($start, $end, $planCategoryArr);
        

    
        return $dataSetArr;
    }

    public static function countClaimTimeSeries($start, $end) 
    {
        // $planCategoryArr = [ 'LSLT', 'LSSE', 'LSSP', 'LSTB'];
        // $planCategoryArr = [ 'LSLT', 'LSSE', 'LSSP', 'LSTB']; HL
        // $planCategoryArr = [ 'LSLT', 'LSSE', 'LSSP', 'LSTB']; AU

        // $data = [];
        // // print_r($this->months);exit();
        // $date_time_category = Yii::$app->session->get("date_time_category");
        // $period = self::getChartPeriodArr($date_time_category);
        // $start = 1606381441;//Monday, January 13, 2020 3:41:52 PM
        // $end = 1621313292; //May 29, 2021 06:29:32 PM
        $planCategoryArr = [ 'LSLT', 'LSSE', 'LSSP', 'LSTB'];

        $dataSetArr = self::getCaseByCategory($start, $end, $planCategoryArr);
        

    
        return $dataSetArr;
    }

    public static function getPlanByCategory($dateStart = 1578930112, $dateEnd = 1622312972,  $planCategoryArr = [ 'LSLT', 'LSSE', 'LSSP', 'LSTB'])
    {
        # load category first
        #->createCommand()->getRawSql()
        $tempArr = [];
        $groupByCategory = UserPlanActionLog::find()->select(["count(created_at) as total","(date_format(FROM_UNIXTIME(created_at), '%Y-%m-%d')) as action_date"])->andWhere(['in', 'plan_category', $planCategoryArr])->andWhere(['between', 'created_at', $dateStart, $dateEnd])->andWhere(['action_status' => UserPlanAction::ACTION_REGISTRATION, 'region_id' => Yii::$app->session->get("region_id")])->groupBy(['action_date'])->orderBy(['action_date' => SORT_ASC])->asArray()->all();

        $groupByTier = UserPlanActionLog::find()->select(["count(plan_tier) as total", "plan_tier", "plan_category", "(date_format(FROM_UNIXTIME(created_at), '%Y-%m-%d' )) as action_date"])->andWhere(['between', 'created_at', $dateStart, $dateEnd])->andWhere(['in', 'plan_category', $planCategoryArr])->andWhere(['action_status' => UserPlanAction::ACTION_REGISTRATION, 'region_id' => Yii::$app->session->get("region_id")])->groupBy(['action_date', 'plan_tier', 'plan_category'])->asArray()->all();

        foreach ($groupByCategory as $cat) {
            # code...
            $tierArr = [];
            foreach ($groupByTier as $t) {
                # code...
                if($cat['action_date'] == $t['action_date']) {
                    // $tierArr[] = $t['plan_tier'];
                    // $tierArr[$t['plan_tier']][] = [$t['plan_category'] => $t['total']];
                    // $tierArr[$t['plan_tier']][$t['plan_category']] = $t['total'];
                    array_push($tierArr, $t);
                }
            }
            $basic = self::formTierByCategory(InstapPlan::BASIC_PLUS , $planCategoryArr, $tierArr);
            $premium = self::formTierByCategory(InstapPlan::PREMIUM , $planCategoryArr, $tierArr);
            $standard = self::formTierByCategory(InstapPlan::STANDARD , $planCategoryArr, $tierArr);
            $c = array($basic, $premium, $standard);

            // $subArr = [];
            // $subArr[] = $tierArr;
            // ksort($subArr);
            $tempData = ['x' =>$cat['action_date'], 'y' =>$cat['total'], 'dataForSubGraph' => $c];
            array_push($tempArr, $tempData);
        }

        return $tempArr;

    }
    // 26-11-2020 - 18-5-2021
    public static function getCaseByCategory($dateStart = 1606381441, $dateEnd = 1621313292,  $planCategoryArr = [ 'LSLT', 'LSSE', 'LSSP', 'LSTB'])
    {
        # load category first
        #->createCommand()->getRawSql()
        $tempArr = [];
        $groupByCategory = UserCaseActionLog::find()->select(["count(created_at) as total","(date_format(FROM_UNIXTIME(created_at), '%Y-%m-%d')) as action_date"])->andWhere(['in', 'plan_category', $planCategoryArr])->andWhere(['between', 'created_at', $dateStart, $dateEnd])->andWhere(['action_status' => UserCaseAction::ACTION_CLAIM_SUBMIT, 'region_id' => Yii::$app->session->get("region_id")])->groupBy(['action_date'])->orderBy(['action_date' => SORT_ASC])->asArray()->all();

        $groupByTier = UserCaseActionLog::find()->select(["count(plan_tier) as total", "plan_tier", "plan_category", "(date_format(FROM_UNIXTIME(created_at), '%Y-%m-%d' )) as action_date"])->andWhere(['between', 'created_at', $dateStart, $dateEnd])->andWhere(['in', 'plan_category', $planCategoryArr])->andWhere(['action_status' => UserCaseAction::ACTION_CLAIM_SUBMIT, 'region_id' => Yii::$app->session->get("region_id")])->groupBy(['action_date', 'plan_tier', 'plan_category'])->asArray()->all();

        foreach ($groupByCategory as $cat) {
            # code...
            $tierArr = [];
            foreach ($groupByTier as $t) {
                # code...
                if($cat['action_date'] == $t['action_date']) {
                    // $tierArr[] = $t['plan_tier'];
                    // $tierArr[$t['plan_tier']][] = [$t['plan_category'] => $t['total']];
                    // $tierArr[$t['plan_tier']][$t['plan_category']] = $t['total'];
                    array_push($tierArr, $t);
                }
            }
            $basic = self::formTierByCategory(InstapPlan::BASIC_PLUS , $planCategoryArr, $tierArr);
            $premium = self::formTierByCategory(InstapPlan::PREMIUM , $planCategoryArr, $tierArr);
            $standard = self::formTierByCategory(InstapPlan::STANDARD , $planCategoryArr, $tierArr);
            $c = array($basic, $premium, $standard);

            // $subArr = [];
            // $subArr[] = $tierArr;
            // ksort($subArr);
            $tempData = ['x' =>$cat['action_date'], 'y' =>$cat['total'], 'dataForSubGraph' => $c];
            array_push($tempArr, $tempData);
        }

        return $tempArr;

    }


    static public function formTierByCategory($tier = 'basic_plus', $planCategoryArr, $planArr) {
        
        $tempArr = [];

        $tempArr['label'] = InstapPlan::allPlanTier()[$tier];
        if(!empty($planCategoryArr) && !empty($planArr)) {
            foreach ($planCategoryArr as $v) {
                $total = 0;
                foreach ($planArr as $p) {
                    if($p['plan_category'] == $v && $p['plan_tier'] == $tier) {
                        $total = $p['total'];
                    }
                }
                $tempArr[$v] = $total;
            }
        }

        return $tempArr;
    }

    public static function countTotalCLaim($plan_id) {
        $data = [];
        $date_time_category = Yii::$app->session->get("date_time_category");
        $period = self::getChartPeriodArr($date_time_category);

        for($i=0; $i<count($period); $i++) {
            $start = $period[$i][0];
            $end = $period[$i][1];
            $total = UserCase::find()->innerJoinWith('planPool', true)->andWhere(['instap_plan_pool.region_id' => Yii::$app->session->get("region_id"), 'instap_plan_pool.plan_id' => $plan_id])->andWhere(['between', 'user_case.created_at', $start, $end ])->count();
         
            array_push($data, $total);
        }

        return $data;
    }

 

    public static function grossMargin($plan_id) {
        $data = [];
        // $date_time_category = Yii::$app->session->get("date_time_category");
        $date_time_category = DashboardForm::THIS_MONTH;
        $period = self::getChartPeriodArr($date_time_category);
        // $period = month  or year or week;
        
        for($i=0; $i<count($period); $i++) {
            $start = $period[$i][0];
            $end = $period[$i][1];
            $plans = InstapPlanPool::find()->innerJoinWith('plan', true)->select(['sum(instap_plan.retail_price) as retailPrice', 'sum(instap_plan.dealer_price) as dealerPrice','instap_plan.id', 'instap_plan_pool.plan_id'])->andWhere(['instap_plan_pool.region_id' => Yii::$app->session->get("region_id"), 'instap_plan_pool.plan_id' => $plan_id])->andWhere(['between', 'instap_plan_pool.created_at', $start, $end])->asArray()->all();
            if($plans[0]['retailPrice'] && $plans[0]['dealerPrice']) {
                $price = $plans[0]['retailPrice'] / $plans[0]['dealerPrice'];
                $grossMargin = number_format((float)$price, 2, '.', '');
            }else{
                $price = 0;
                $grossMargin = number_format((float)$price, 2, '.', '');
            }

         
            array_push($data, $grossMargin);
        }

        return $data;
    }

    public static function grossSales() {
        //toDO: replace updated_at to created_at in production
        $tempArr = [];
        $poolArr = InstapPlanPool::find()->innerJoinWith('plan', false)->select(['sum(instap_plan.dealer_price) as totalSales', "(date_format(FROM_UNIXTIME(instap_plan_pool.created_at), '2020-%m')) as sales_date"])->andWhere(['not in', 'instap_plan_pool.plan_status', [InstapPlanPool::STATUS_CANCEL]])->andWhere(['instap_plan_pool.region_id' => Yii::$app->session->get('region_id')])->groupBy(['sales_date'])->orderBy(['sales_date' => SORT_ASC])->asArray()->all();
        
        foreach ($poolArr as $v) {
            # code...
            $tempData = ['x' =>$v['sales_date'], 'y' =>$v['totalSales']];
            array_push($tempArr, $tempData);

        }

        return $tempArr;
    }

    public static function createChartJson($plans, $type) 
    {
        // $plans = InstapPlan::find()->select(['id', 'name'])->andWhere(['region_id' => 'SG'])->asArray()->all();
        $data = [];
        $value = 0;
        foreach ($plans as $v) {
            $o = (object) [];
            if($type == "claim") {
                $d = self::countTotalCLaim($v['id']);
            }else if($type == "plan") {
                $d = self::countTotalPlanRegistration($v['id']);
            }else if($type == "gross_margin"){
                $d = self::grossMargin($v['id']);
            }
            $o->plan_id = $v['id'];
            $o->name = $v['name'];
            $o->value = $d;
            $data[] = $o;

        }
        return $data;
    }

    //date time category = week, day, year, month
    static public function getChartPeriodArr($date_time_category) {
        //GET TEMPO BY CATEGORY
        $tempo = ($date_time_category == 'year')? DashboardForm::MONTH_IN_YEAR : (($date_time_category == DashboardForm::THIS_MONTH) ? Utility::dayInMonth(date('m')) : DashboardForm::DAY_IN_CURRENT_WEEK);

        $data = [];
        for($i=0; $i<count($tempo); $i++) {
            $temp_arr = [];
            if($date_time_category == "year") {
                $start = strtotime( $tempo[$i].date("Y"));
                $end = strtotime( $tempo[$i].date("Y")."+1 month"."-1 second");
            }else if ($date_time_category == DashboardForm::THIS_MONTH) {
                $start = strtotime($tempo[$i]);
                $end = strtotime( $tempo[$i]."23:59:59");
            } else {
                $start = strtotime($tempo[$i]);
                $end = strtotime( $tempo[$i]."23:59:59");
            }
            //start and end
            $temp_arr = [$start, $end];

            array_push($data, $temp_arr);
        }

        return $data;

        // $start = strtotime( $months[$i].date("Y"));
        // $end = strtotime( $months[$i].date("Y")."+1 month"."-1 second");

    }

    //HTML here//
    public static function mostSoldPlanByCompany($start_date, $end_date) {
        $html = "";

        $m = InstapPlanPool::find()->select(['dealer_company_id', 'count(dealer_company_id) as c'])->andWhere(['not in', 'plan_status', [InstapPlanPool::STATUS_CANCEL]])->andWhere(['region_id' => Yii::$app->session->get('region_id')])->groupBy(['dealer_company_id'])->andWhere(['between', 'created_at', $start_date, $end_date])->orderBy(['c' => SORT_DESC])->limit(5)->asArray()->all();

        $html .= "<table class='table'><thead><tr>";
        $html .= "<th width='250'>Dealer</th>";
        $html .= "<th width='*'>Number of policy sold</th>";
        $html .= "</tr></thead>";
        $html .= "<tbody>";

        if(empty($m)) {
            $html .= "<tr><td>No record found.</td></tr>";
        } else {
            foreach ($m as $v) {
                $company = DealerCompany::find()->where(['id' => $v['dealer_company_id']])->one();
                $company_name = Html::a($company->business_name, Url::to(["dealer-company/view", 'id'=>$v['dealer_company_id']]));
                $html .= "<tr><td>".$company_name."</td>";
                $html .= "<td>".$v['c']."</td></tr>";
            }    
        }
        
        $html .= "</tbody></table>";

        return $html;
    }

    public static function mostSoldPlanByStaff($start_date, $end_date) {
        $html = "";
        $plan_pool = InstapPlanPool::find()->select(['id'])->andWhere(['not in', 'plan_status', [InstapPlanPool::STATUS_CANCEL]])->andWhere(['region_id' => Yii::$app->session->get('region_id')])->asArray()->all();
        $plan_pool_arr = array_column($plan_pool, 'id');
        $m = DealerOrder::find()->select(['dealer_user_id', 'count(dealer_user_id) as c'])->andWhere(['in','plan_pool_id', $plan_pool_arr])->andWhere(['between', 'created_at', $start_date, $end_date])->groupBy(['dealer_user_id'])->orderBy(['count(dealer_user_id)' => SORT_DESC])->limit(5)->asArray()->all();

        $html .= "<table class='table'><thead><tr>";
        $html .= "<th width='250'>Retail Assistance</th>";
        $html .= "<th width='*'>Number of policy sold</th>";
        $html .= "</tr></thead>";
        $html .= "<tbody>";

        if(empty($m)) {
            $html .= "<tr><td>No record found.</td></tr>";
        } else {
            foreach ($m as $v) {
                $user = User::find()->where(['id' => $v['dealer_user_id']])->one();
                $name = Html::a($user->userProfile->first_name." ".$user->userProfile->last_name, Url::to(["dealer-user/view", 'id'=>$v['dealer_user_id']]));
                $html .= "<tr><td>".$name."</td>";
                $html .= "<td>".$v['c']."</td></tr>";
            }
        }
        $html .= "</tbody></table>";


        return $html;
    }

}
