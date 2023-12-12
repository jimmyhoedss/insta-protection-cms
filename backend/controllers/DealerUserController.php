<?php

namespace backend\controllers;

use Yii;
use common\models\DealerUser;
use common\models\User;
use common\models\DealerUserHistory;
use common\models\search\DealerUserSearch;
use common\models\search\DealerOrderSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use api\components\CustomHttpException;
use common\components\MyCustomActiveRecord;


/**
 * DealerUserController implements the CRUD actions for DealerUser model.
 */
class DealerUserController extends Controller
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
                    // 'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel = new DealerUserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {   
        $u =  $this->findModel($id)->user_id;
        $searchModel = new DealerOrderSearch();
        $searchModel->setDealerUserId($u);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('view', [
            'model' => $this->findModel($id),
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionCreate($route = null, $route_id = null)
    {
        $model = new DealerUser();
        $model->scenario = DealerUser::SCENARIO_CMS;
        $success = false;
        //auto select company if route from dealer company page
        if(isset($route_id)) {
            $model->dealer_company_id = $route_id;
        }

            if ($model->load(Yii::$app->request->post())) {
                $role = Yii::$app->request->post('DealerUser')['roles'];
                $auth = Yii::$app->authManager;
                $dealerUser = DealerUser::find()->andWhere(["user_id"=>$model->user_id])->one();

                $transaction = Yii::$app->db->beginTransaction();
                try{
                    //if dealer exist as disabled
                    if($dealerUser) { 
                        if($dealerUser->status === MyCustomActiveRecord::STATUS_DISABLED) {
                            $dealerUser->status = MyCustomActiveRecord::STATUS_ENABLED;
                            $dealerUser->dealer_company_id = $model->dealer_company_id;
                            $auth->assign($auth->getRole($role), $dealerUser->user_id);
                            $dealer_user_history = DealerUserHistory::makeModel($dealerUser->user_id, $model->dealer_company_id, DealerUserHistory::ACTION_ADD_ROLE, $role);
                            if($dealerUser->save() && $dealer_user_history->save()) {
                                $success = true;
                            }
                        } else {
                            Yii::$app->session->setFlash('error', "This user is currently under another dealer!");
                            return $this->redirect('index');
                        }
                    } else {
                            //add new dealer 
                            if($model->save()) {
                                $auth->assign($auth->getRole($role), $model->user_id);
                                $dealer_user_history = DealerUserHistory::makeModel($model->user_id, $model->dealer_company_id, DealerUserHistory::ACTION_ADD_ROLE, $role);
                                if($dealer_user_history->save()) {
                                    $success = true;
                                }
                            }          
                        }
                }
                catch (yii\db\IntegrityException $e) {
                    $success = false;
                }

                if($success) {
                    $transaction->commit();
                    Yii::$app->session->setFlash('success', "User is added !");
                    if($route != null) {
                        return $this->redirect([$route, 'id' => $route_id]);
                    }
                    return $this->redirect('index');

                } else {
                    $transaction->rollback();
                    $model->addError('user_id', Yii::t('common', "Fail to add dealer"));
                } 

            // } else {
            //     Yii::$app->session->setFlash('error', "This user is currently under another dealer!");
            // }
         
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionUpdate($id, $route = null, $route_id = null)
    {
        $success = false;
        $model = $this->findModel($id);
        $auth = Yii::$app->authManager;
        $userId = $model->user_id;
        $roles = $auth->getRolesByUser($userId);
        $r = array_keys($roles);
        // print_r($r);exit();
        $model->roles = $r;
        $previousCompany = $model->dealer_company_id;

        if ($model->load(Yii::$app->request->post()) ) {
            $transaction = Yii::$app->db->beginTransaction();
            try{

                $auth = Yii::$app->authManager;
                $model->roles = Yii::$app->request->post('DealerUser')['roles'];

                if($previousCompany != $model->dealer_company_id) {
                    //remove role from previous company
                    $history1 = DealerUserHistory::makeModel($userId, $previousCompany, DealerUserHistory::ACTION_REMOVE_ROLE, $model->roles);
                    // record history in current company
                    $history2 = DealerUserHistory::makeModel($userId, $model->dealer_company_id, DealerUserHistory::ACTION_ADD_ROLE, $model->roles);
                    DealerUser::revokeDealerRolesByUserId($userId, $auth);
                    

                } else {
                    //remove all the role
                    DealerUser::revokeDealerRolesByUserId($userId, $auth);
                    $history3 = DealerUserHistory::makeModel($userId, $model->dealer_company_id, DealerUserHistory::ACTION_CHANGE_ROLE, $model->roles);
                    $history3->save();


                }
                if($model->save()){
                    if(isset($history1) && isset($history2)) {
                        if($history1->save() && $history2->save()) {
                            $success = true;
                        }
                    }
                    if(isset($history3)) {
                        if($history3->Save()) {
                            $success = true;
                        }
                    }
                    $auth->assign($auth->getRole($model->roles), $model->user_id);
                }            

            } catch (yii\db\IntegrityException $e) {
                $success = false;
                $transaction->rollback();
                Yii::$app->session->setFlash('error', Yii::t('backend', 'Fail to update.'));
            }

            if($success){
                $transaction->commit();
                Yii::$app->session->setFlash('success', Yii::t('backend', 'Update success.'));
                //to return to respective controller
                if($route != null) {
                    return $this->redirect([$route, 'id' => $route_id]);
                }
                return $this->redirect(['index']);

            } else {
                $transaction->rollback();
                Yii::$app->session->setFlash('error',  Yii::t('backend', 'Fail to update.'));
            }

        }

        return $this->render('update', [
            'model' => $model
        ]);
    }

    // public function removeDealerRole($model, $auth, $action) {
    //     $item = $auth->getRolesByUser($model->user_id);
    //     for($i=0; $i<count($item); $i++) {
    //         $role = array_values($item)[$i];
    //         $role_current = $model->roles ? $model->roles : $role->name;
    //         if($role->name == User::ROLE_DEALER_MANAGER || $role->name == User::ROLE_DEALER_ASSOCIATE) {
    //             $dealer_user_history = DealerUserHistory::makeModel($model->user_id, $model->dealer_company_id, $action, $role_current);
    //             $dealer_user_history->save();
    //             $auth->revoke($role,$model->user_id);
    //         }
    //     }
    //     // return $role_current;
    // }

    public function actionDelete($id, $route = null, $route_id = null)
    {
        $model = $this->findModel($id);
        $success = false;
        if($model){
            $transaction = Yii::$app->db->beginTransaction();
            try{

                $duId = $model->user_id;
                $du_auth = Yii::$app->authManager;
                $item = $du_auth->getRolesByUser($duId);
                for($i=0; $i<count($item); $i++) {
                    $role = array_values($item)[$i];
                    $role_current = $model->roles ? $model->roles : $role->name;
                    if($role->name == User::ROLE_DEALER_MANAGER || $role->name == User::ROLE_DEALER_ASSOCIATE) {
                        $dealer_user_history = DealerUserHistory::makeModel($duId, $model->dealer_company_id, DealerUserHistory::ACTION_REMOVE_ROLE, $role_current);
                        $dealer_user_history->save();
                        $du_auth->revoke($role,$model->user_id);
                    }
                }
                // $this->removeDealerRole($model, $du_auth, DealerUserHistory::ACTION_REMOVE_ROLE);
                $model->status = MyCustomActiveRecord::STATUS_DISABLED;
                if($model->save()) {
                    $success = true;
                }
            
            } catch (yii\db\IntegrityException $e) {
                //print_r($e);
                //exit();
                    $success = false;
                }

            if($success){
                $transaction->commit();
                Yii::$app->session->setFlash('success', "Dealer User Deleted !");
                if($route != null) {
                    return $this->redirect([$route, 'id' => $route_id]);
                }

            }else {
                $transaction->rollback();
                Yii::$app->session->setFlash('error', "Delete failed!");
            }

            return $this->redirect(['index']);
        }
    }

    protected function findModel($id)
    {
        if (($model = DealerUser::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('backend', 'The requested page does not exist.'));
    }


}
