<?php

namespace backend\controllers;

use Yii;
use common\models\DealerCompany;
use common\models\DealerCompanyDealer;
use common\models\InstapPlanDealerCompany;
use common\models\search\DealerCompanySearch;
use common\models\search\DealerOrderSearch;
use common\models\search\DealerUserSearch;
use common\models\form\CompanyPlanForm;

use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\filters\AccessControl;


/**
 * DealerController implements the CRUD actions for DealerCompany model.
 */
class DealerCompanyController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                       //'actions' => ['index'],
                       'allow' => '@',
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all DealerCompany models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new DealerCompanySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'page'=>"index"
        ]);
    }

    public function actionView($id)
    {   
        $searchModel = new DealerOrderSearch();
        $searchModel->setDealerId($id);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $searchModel_DU = new DealerUserSearch();
        $searchModel_DU->setDealerId($id);
        $dataProvider_DU = $searchModel_DU->search(Yii::$app->request->queryParams);

        return $this->render('view', [
            'model' => $this->findModel($id),
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'searchModel_DU' => $searchModel_DU,
            'dataProvider_DU' =>  $dataProvider_DU,
        ]);
    }
     //company organization chart for admin view
    public function actionChartAdmin($id)
    {   
        $model = $this->findModel($id);
        $arr = DealerCompanyDealer::find()->asArray()->all();
        $topmost_id = DealerCompany::findTopmostCompany($model->id,$arr);
        $comp_arr = DealerCompany::listOrganization($arr,$topmost_id);
        return $this->render('org_chart', [
            'model' => $model,
            'topmost_id' => $topmost_id,
            'comp_arr' => $comp_arr
        ]);
    }

    //company organization chart
    public function actionChart($id)
    {   
        $model = $this->findModel($id);
        $company_arr = DealerCompanyDealer::find()->asArray()->all();
        $grandParent = DealerCompany::findUplinePath($id, $company_arr);
        $grandChildren = DealerCompany::grandChildren($company_arr, $id);

        //not able to view company sibling
        $all_company = array_merge($grandChildren, $grandParent);
        $topmost_id = DealerCompany::findTopmostCompany($model->id,$all_company);
        $comp_arr = DealerCompany::listOrganization($all_company,$topmost_id);
       
        return $this->render('org_chart', [
            'model' => $model,
            'topmost_id' => $topmost_id,
            'comp_arr' => $comp_arr
        ]);
    }

    public function actionCreate()
    {
        $model = new DealerCompany();
        $modelCompanyPlan = new CompanyPlanForm();
        $modelCompanyRelation = new DealerCompanyDealer();
        $success = false;

        if ($model->load(Yii::$app->request->post()) && $modelCompanyPlan->load(Yii::$app->request->post()) && $modelCompanyRelation->load(Yii::$app->request->post())) {
            // print_r($modelCompanyRelation->dealer_company_upline_id);exit();
            $transaction = Yii::$app->db->beginTransaction();
             //save first to get id
            try {
                //Save twice because need to get new created company id first
                if($model->save()) {
                    $model = $this->prepareCompanyModelPlan($model, $modelCompanyPlan);
                    if($model->save()) {
                        //assign company upline 
                        if(!empty($modelCompanyRelation->dealer_company_upline_id)) {
                            $modelCompanyRelation->dealer_company_downline_id = $model->id;
                            $modelCompanyRelation->save();
                        }
                        $success = true;
                    }
                }
             } catch (yii\db\IntegrityException $e) {
                $success = false;
             }

             if($success) {
                $transaction->commit();
                Yii::$app->session->setFlash('success', "Save successful!");
                return $this->redirect(['index']);
             } else {
                $transaction->rollback();
                Yii::$app->session->setFlash('error', "Unable to save!");
             }
            
        }
        return $this->render('create', [
            'model' => $model,
            'modelCompanyPlan' => $modelCompanyPlan,
            'modelCompanyRelation' => $modelCompanyRelation
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $modelCompanyPlan = new CompanyPlanForm();
        $modelCompanyRelation = new DealerCompanyDealer();
        $uplineExist = DealerCompanyDealer::getUpline($id);
        //use to display upline company in form
        if($uplineExist) {
            $modelCompanyRelation->dealer_company_upline_id = $uplineExist->dealer_company_upline_id;
        }
        $company_plans = InstapPlanDealerCompany::find()->where(['dealer_company_id' => $id])->asArray()->all();
        $plan_id_arr = array_column($company_plans, 'plan_id');
        $modelCompanyPlan->plan_id_arr = $plan_id_arr;
        $success = false;

        if($model->load(Yii::$app->request->post()) && $modelCompanyPlan->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();
            try{
                //clear all first
                if($company_plans) {
                    InstapPlanDealerCompany::deleteAll('dealer_company_id = :dealer_company_id', [':dealer_company_id' => $id]);
                }

                $model = $this->prepareCompanyModelPlan($model, $modelCompanyPlan);
                if($model->save()) {
                    $success = true;
                }
                
                
            } catch (yii\db\IntegrityException $e) {
                $success = false;
            }
            
            if ($success) {
                $transaction->commit();
                Yii::$app->session->setFlash('success', "update successful!");
                return $this->redirect(['index']);
            } else {
                 $transaction->rollback();
                 Yii::$app->session->setFlash('error', "update fail!");
                 return $this->redirect(['index']);
            }
        }

        return $this->render('update', [
            'model' => $model,
            'modelCompanyPlan' => $modelCompanyPlan,
            'modelCompanyRelation' => $modelCompanyRelation
        ]);
    }

    public function prepareCompanyModelPlan($model, $modelCompanyPlan) {

        if($model->sp_inventory_order_mode == DealerCompany::INVENTORY_MODE_AD_HOC) {
            $model->sp_inventory_allocation_mode = DealerCompany::ALLOCATION_MODE_NONE;
        }
        $plan_id_arr = $modelCompanyPlan->plan_id_arr;
        if(!empty($plan_id_arr)) {
            foreach($plan_id_arr as $plan_id) {
                $company_plan = InstapPlanDealerCompany::makeModel($plan_id, $model->id);
                $company_plan->save();
            }
        }
        return $model;
    }

    protected function findModel($id)
    {
        if (($model = DealerCompany::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('backend', 'The requested page does not exist.'));
    }

    
}
