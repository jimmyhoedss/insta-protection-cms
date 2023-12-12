<?php

namespace backend\controllers;

use Yii;
use common\models\DealerOrderInventoryOverview;
use common\models\DealerUser;
use common\models\User;
use common\models\DealerCompany;
use common\models\search\DealerCompanySearch;
use common\models\DealerInventoryAllocationHistory;
use common\models\search\DealerOrderInventoryOverviewSearch;
use common\models\search\DealerInventoryAllocationHistorySearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;


/**
 * DealerOrderInventoryOverviewController implements the CRUD actions for DealerOrderInventoryOverview model.
 */
class DealerOrderInventoryOverviewController extends Controller
{
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
                       'allow' => true,
                       'roles' => [User::ROLE_IP_ADMINISTRATOR, User::ROLE_IP_MANAGER, User::ROLE_IP_ADMIN_ASSISTANT],
                    ],
                ],
            ],
            //page cache strategy
            // 'pageCache' => [
            //     'class' => 'yii\filters\PageCache',
            //     'only' => ['index'],
            //     'duration' => 120,
            //     // 'dependency' => [ //determine when to flush cache
            //     //     'class' => 'yii\caching\DbDependency',
            //     //     'sql' => 'SELECT count(*) FROM dealer_company',
            //     // ],
            //     'variations' => [  //List of factors that would cause the variation of the content being cached (eg, language , or get request)
            //         \Yii::$app->language,
            //         $_GET //e
            //     ]
            // ],
        ];
    }

    public function actionIndex()
    {   
        $searchModel = new DealerCompanySearch();   
        $searchModel->order_mode = DealerCompany::INVENTORY_MODE_STOCKPILE;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionInventoryDetail($id)
    {   
        $model = DealerOrderInventoryOverview::findOne($id);
        return $this->render('inventory-detail', [
            'model' => $model,
        ]);
    }

    public function actionView($id)
    {
        $model = DealerOrderInventoryOverview::find()->where(['dealer_company_id' => $id])->one();
        // $model = $this->findModel($id);
        $searchModel = new DealerInventoryAllocationHistorySearch();
        $searchModel->setCompanyId($id);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        if($model) {
            return $this->render('view', [
                'model' => $model,
                'dataProvider' => $dataProvider
            ]);
        } else {
            throw new NotFoundHttpException(Yii::t('backend', 'No plans allocated in this company.'));
        }
    }


    public function actionAllocate()
    {
        $model = new DealerOrderInventoryOverview();
        $model->scenario = DealerOrderInventoryOverview::SCENARIO_CMS_ALLOCATE;
        $user = Yii::$app->user;
        $ip = DealerUser::getDealerFromUserId($user);
        $success = false;
        // var_dump($ip);
        // exit();
        if ($model->load(Yii::$app->request->post())) {
            if(empty($ip)) {
                Yii::$app->session->setFlash('error', "Unable to allocate stock, please join a company. ");
                return $this->redirect(['index']);
             }
            $transaction = Yii::$app->db->beginTransaction();
            $m = DealerOrderInventoryOverview::find()->andWhere(['dealer_company_id' => $model->dealer_company_id])->andWhere(['plan_id' =>$model->plan_id])->one();
            $history = DealerInventoryAllocationHistory::makeModel($ip->id, $model->dealer_company_id, $model->assign_amount, $model->plan_id, DealerInventoryAllocationHistory::ACTION_ALLOCATE);
            
            if($history->save()) {
                if($m) {
                    $q = $m->quota + $model->assign_amount;
                    $o = $m->overall + $model->assign_amount;
                    //check update attribytes in array
                    // $m->updateAttributes(['quota' => $q]);
                    // $m->updateAttributes(['overall' => $o]);
                    $m->quota = $q;
                    $m->overall = $o;
                    $m->save();
                    $success = true;
                }else {
                    $o = $model->assign_amount;
                    $model->quota = $o;
                    $model->overall = $o;
                    $model->save();
                    $success = true;
                }               
            }

            if($success) {
                $transaction->commit();
                Yii::$app->session->setFlash('success', "Successfully allocated ".$model->assign_amount." ".$model->plan->name. " to ". $model->dealer->business_name);
                return $this->redirect(['index']);

            }else {
                $transaction->rollback();
                Yii::$app->session->setFlash('Error', "Unbable allocate stock");
                return $this->redirect(['index']);
            }


        }
        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $searchModel = new DealerInventoryAllocationHistorySearch();
        $searchModel->setCompanyId($id);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $model->dealer_company_id = $id;
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
            'dataProvider' => $dataProvider
        ]);
    }

    protected function findModel($id)
    {
        if (($model = DealerOrderInventoryOverview::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('backend', 'The requested page does not exist.'));
    }

}
