<?php

namespace backend\controllers;

use Yii;
use common\models\DealerOrderInventoryOverview;
use common\models\DealerOrderInventory;
use common\models\DealerUser;
use common\models\User;
use common\models\DealerCompany;
use common\models\DealerCompanyDealer;
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
                    [
                       'actions' => ['revert'],
                       'allow' => true,
                       'roles' => [User::ROLE_IP_ADMINISTRATOR],
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

    public function actionLowInventory($amount)
    {   
        $model = DealerOrderInventoryOverview::find()->distinct()->select(["dealer_company_id"])->andWhere(["<=", "quota", $amount])->asArray()->all();
        $companyIdArr = array_column($model, "dealer_company_id");
        $searchModel = new DealerCompanySearch();   
        // $searchModel->order_mode = DealerCompany::INVENTORY_MODE_STOCKPILE;
        $searchModel->searchByCompanyID($companyIdArr);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
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

    public function actionRevert()
    {
        $model = new DealerOrderInventoryOverview();
        $model->scenario = DealerOrderInventoryOverview::SCENARIO_CMS_REVERT;
        $user = Yii::$app->user;
        $ip = DealerUser::getDealerFromUserId($user);
        $success = false;

        if ($model->load(Yii::$app->request->post())) {
            try {
                $quota = $overall = 0;
                $revert_amount = $model->assign_amount;
                $transaction = Yii::$app->db->beginTransaction();
                $revert_action = DealerInventoryAllocationHistory::mapModeToAction()[$model->mode_stock_revert];

                $inventory = DealerOrderInventoryOverview::find()->andWhere(['dealer_company_id' => $model->dealer_company_id])->andWhere(['plan_id' =>$model->plan_id])->one();

                if(empty($ip)) {
                    Yii::$app->session->setFlash('error', "Unable to allocate stock, please join a company. ");

                    return $this->redirect(['index']);
                }

                if(!$inventory) {
                    Yii::$app->session->setFlash('error', "Company inventory not found");
                    return $this->redirect(['revert']);
                }
                //if reverting misallocation stock, then we only need to check its inventory quota
                if($revert_action != DealerInventoryAllocationHistory::ACTION_REVERT_ACTIVATE) {
                    if($inventory->quota - $revert_amount < 0) {
                        Yii::$app->session->setFlash('error', "Insufficient amount to revert");
                        return $this->redirect(['revert']);
                    }
                }
                
                $revert_upline_id = $ip->id;
                $company_name = $ip->business_name;
                //check if amount is enough to revert
                if($revert_action == DealerInventoryAllocationHistory::ACTION_REVERT_ACTIVATE) {
                    //get unsold stock
                    $revert_upline_id = $model->dealer_company_id;
                    $company_name = $model->dealer->business_name;
                    $unsold_stock = DealerOrderInventory::retrieveAllAvailableStock($model->plan_id, $model->dealer_company_id);

                    if(count($unsold_stock) - $revert_amount < 0) {
                        Yii::$app->session->setFlash('error', "Revert amount cannot more than total activated stock");
                        return $this->redirect(['revert']);
                    }

                    $quota = $inventory->quota + $revert_amount;
                    $overall = $inventory->overall; 
                    $i = 0;

                    foreach ($unsold_stock as $stock) {
                        $stock->delete();
                        $i++;
                        if($i == $revert_amount) {
                            break;
                        }
                    }

                } else {
                    //action revert allocate
                    $quota = $inventory->quota - $revert_amount;
                    $overall = $inventory->overall - $revert_amount;

                    if($model->mode_stock_revert == DealerOrderInventoryOverview::REVERT_IP_ALLOCATE) {
                        $check_upline = DealerCompanyDealer::getUpline($model->dealer_company_id);
                        if($check_upline) {
                            Yii::$app->session->setFlash('error', "Cannot perform IP revert, because this company has upline.");
                            return $this->redirect(['revert']);
                        }

                    }

                    if($model->mode_stock_revert == DealerOrderInventoryOverview::REVERT_DEALER_ALLOCATE) {
                        //add back amount for dealer
                        $upline_inventory = $inventory->getUplineInventory();
                        if(!$upline_inventory) {
                            Yii::$app->session->setFlash('error', "Cannot perform dealer revert stock, because no upline in this company.");
                            return $this->redirect(['revert']);
                        }

                        $company_name = $upline_inventory->dealer->business_name;
                        $revert_upline_id = $upline_inventory->dealer_company_id;
                        $upline_quota = $upline_inventory->quota + $revert_amount;
                        $upline_inventory->quota = $upline_quota;
                        $upline_inventory->save();
                    }
                }


                $history = DealerInventoryAllocationHistory::makeModel($model->dealer_company_id, $revert_upline_id, -1 * $revert_amount, $model->plan_id, $revert_action);
               
                $inventory->quota = $quota;
                $inventory->overall = $overall;
                $inventory->save();
                $history->save();


                $success = true;
                               

            } catch (Exception $e) {
                $transaction->rollback();
            }

            if($success) {
                $transaction->commit();
                $msg = "Successfully revert ".$model->assign_amount." ".$model->plan->name. " to ". $company_name;
                Yii::$app->session->setFlash('success', $msg);

            }else {
                $transaction->rollback();
                Yii::$app->session->setFlash('error', "Unbable revert stock");
            }


            // return $this->redirect(['index']);

        }


        return $this->render('revert', [
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
