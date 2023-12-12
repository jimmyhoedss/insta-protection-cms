<?php

namespace backend\controllers;

use Yii;
use common\models\User;
use common\models\UserPlan;
use common\models\UserPlanAction;
use common\models\UserPlanActionLog;
use common\models\InstapPlanPool;
use common\models\UserPlanDetailEdit;
use common\models\UserPlanDetailEditHistory;
use common\models\UserPlanDetail;
use common\models\UserPlanActionDocument;


use common\models\search\UserPlanActionSearch;
use common\models\search\InstapPlanPoolSearch;
use common\models\search\UserPlanDetailEditSearch;
use common\models\search\UserPlanDetailEditHistorySearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

use yii\filters\VerbFilter;
use yii\filters\AccessControl;

use api\components\CustomHttpException;
use common\models\fcm\FcmPlanStatusChanged;
use common\models\form\PlanActionForm;

/**
 * InstapPlanPoolController implements the CRUD actions for InstapPlanPool model.
 */
class InstapPlanPoolController extends Controller
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
                   'actions' => ['index','update','pending-approval','pending-edit-approval','create','view','delete','find-model','edit-history'],
                   'allow' => true,
                   'roles' => ['@'],
                ],
                [
                   'actions' => ['edit-approve','edit-reject'],
                   'allow' => true,
                   // 'roles' => [User::ROLE_ADMINISTRATOR],
                   'permissions' =>[User::PERMISSION_IP_APPROVE],
                   /*'matchCallback' => function ($rule, $action) {
                            // return date('d-m') === '31-10';
                        return Yii::$app->user->can(User::PERMISSION_IP_APPROVE);
                    }*/
                ],
                
            ],
        ],
        ];
    }




    /**
     * Lists all InstapPlanPool models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new InstapPlanPoolSearch();
        $searchModel->setPlanController();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'page'=>"index"
        ]);
    }


    public function actionUpdate($id) {

        $model = $this->findModel($id);
        $modelPlan = $this->findModelPlan($id);
        $modelPlanDetail = $this->findModelPlanDetail($id);
        $searchModel = new UserPlanActionSearch();
        $searchModel->setPlanPoolId($id);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
  
        $planActionForm = new PlanActionForm(); //for different image type upload
        $modelAction = new UserPlanAction();
        $modelPlanDetail2 = new UserPlanDetail();

        if ($modelAction->load(Yii::$app->request->post()) && $modelPlanDetail2->load(Yii::$app->request->post()) && $planActionForm->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();
            $success = false;
            try{
                $modelAction->plan_pool_id  = $model->id;
                //check plan status not null for dropdown select
                if($modelAction->action_status === null) {
                    Yii::$app->session->setFlash('error', "Plan status cannot be empty, please select a status before update");
                    return $this->redirect(['update', 'id' => $id]);
                }

                //check when action approve
                if($modelAction->action_status === UserPlanAction::ACTION_APPROVE || $modelAction->action_status === UserPlanAction::ACTION_REQUIRE_CLARIFICATION) {
                    // $hasRegister = UserPlanDetail::isPlanRegistered($model, $modelAction->action_status);
                    //required clarification need user to check is plan panding approval
                    $hasRegister = $model->checkRegtrationChecklistComplete();
                    if(!$hasRegister) {
                        Yii::$app->session->setFlash('error', "action cannot be done, because plan registration is not completed.");
                        return $this->redirect(['update', 'id' => $id]);
                    } else {
                        //device purchase price cannot be empty
                        if($modelAction->action_status === UserPlanAction::ACTION_APPROVE) {
                            $modelPlanDetail->scenario = UserPlanDetail::SCENARIO_REQUIRE_PURCHASE_PRICE;
                            $modelAction->notes_user = $modelPlanDetail->sp_device_purchase_price = $modelPlanDetail2->sp_device_purchase_price;   
                            $modelPlanDetail->save();   
                            if($modelPlanDetail->hasErrors()) {
                                $err = "";
                                foreach ($modelPlanDetail->getFirstErrors() as $name => $error) {
                                    $err .= $error . "<br>";
                                }
                                Yii::$app->session->setFlash('error', $err);
                                return $this->redirect(['update', 'id' => $id]);
                            }     
                        }
                    }
                    //admin can assist dealer to upload assessment photo if plan is pending registration. 
                    //action upload_photo_admin for internal review, check scenario
                } else if($modelAction->action_status === UserPlanAction::ACTION_PHYSICAL_ASSESSMENT || $modelAction->action_status === UserPlanAction::ACTION_UPLOAD_PHOTO) {
                        if($model->plan_status === InstapPlanPool::STATUS_PENDING_REGISTRATION) {
                            $planActionForm->scenario = ($modelAction->action_status === UserPlanAction::ACTION_PHYSICAL_ASSESSMENT) ? planActionForm::PHOTO_ASSESS : planActionForm::PHOTO_REG;
                        } else {
                            Yii::$app->session->setFlash('error', "cannot upload photo, plan status is not pending register");
                            return $this->redirect(['update', 'id' => $id]);
                        }

                    } else if($modelAction->action_status === UserPlanAction::ACTION_UPLOAD_PHOTO_ADMIN) {
                        $planActionForm->scenario = planActionForm::PHOTO_REG;
                    }

                //Oh: do not map to status if action is ACTION_UPLOAD_PHOTO_ADMIN    
                if($modelAction->action_status !== UserPlanAction::ACTION_UPLOAD_PHOTO_ADMIN) {
                    $planStatus = InstapPlanPool::mapActionToStatus()[$modelAction->action_status];
                    $model->plan_status = $modelPlan->current_plan_status = $planStatus;
                }
                $actionLog = UserPlanActionLog::makeModel($model, $modelAction->action_status);
                //UserPlan model redundancy

                if ($planActionForm->validate() && $modelAction->save() && $model->save() && $modelPlan->save() && $actionLog->save()) {
                    //oh: retrive plan_action id first then save document
                    if($modelAction->action_status === UserPlanAction::ACTION_PHYSICAL_ASSESSMENT || $modelAction->action_status === UserPlanAction::ACTION_UPLOAD_PHOTO || $modelAction->action_status === UserPlanAction::ACTION_UPLOAD_PHOTO_ADMIN) {
                        $doc_type = UserPlanActionDocument::mapActionToDocumentType()[$modelAction->action_status];
                        //oh: admin upload photo action use photo_registration attribute
                        $thumbnail = ($modelAction->action_status === UserPlanAction::ACTION_PHYSICAL_ASSESSMENT) ? $planActionForm->photo_assessment : $planActionForm->photo_registration;
                        UserPlanActionDocument::uploadDocument($modelAction,  $thumbnail, $doc_type);
                    }
                    //notify user if not the below status
                    if($modelAction->action_status !== UserPlanAction::ACTION_UPLOAD_PHOTO&& $modelAction->action_status !== UserPlanAction::ACTION_PHYSICAL_ASSESSMENT && $modelAction->action_status !== UserPlanAction::ACTION_UPLOAD_PHOTO_ADMIN ) {
                        $fcm = new FcmPlanStatusChanged($model);
                        $fcm->send();
                    }

                    $modelAction = new UserPlanAction();
                    $success = true;
                }
            } catch (yii\db\IntegrityException $e) {
                $success = false;                
                $str = Utility::jsonifyError("", "database error", CustomHttpException::KEY_UNEXPECTED_ERROR);
                throw new CustomHttpException($str, CustomHttpException::UNPROCESSABLE_ENTITY);
            }


            if ($success) {
                $transaction->commit();
                //Yii::warning('transaction done');
                Yii::$app->session->setFlash('success', "Update successful");
                return $this->refresh(); //Use refresh to prevent form resubmission from refreshing page.
            } else {  
                $msg = "";
                if ($modelAction->hasErrors()){
                    foreach ($modelAction->getFirstErrors() as $name => $error) {
                        $msg .= $error . "<br>";
                    }
                }
                if($model->hasErrors()){
                    foreach ($model->getFirstErrors() as $name => $error) {
                        $msg .= $error . "<br>";
                    }
                }
                if($modelPlan->hasErrors()) {
                    foreach ($modelPlan->getFirstErrors() as $name => $error) {
                        $msg .= $error . "<br>";
                    }
                }
                if($planActionForm->hasErrors()) {
                    foreach ($planActionForm->getFirstErrors() as $name => $error) {
                        $msg .= $error . "<br>";
                    }
                }
                if($actionLog->hasErrors()) {
                    foreach ($actionLog->getFirstErrors() as $name => $error) {
                        $msg .= $error . "<br>";
                    }
                }
                Yii::$app->session->setFlash('error', $msg);
                $transaction->rollback();
                //Yii::warning('transaction rollback');
            }
            
        }

        return $this->render('update', [
            'model' => $model,
            'modelAction' => $modelAction,
            'modelPlanDetail' =>$modelPlanDetail,
            'modelPlanDetail2' =>$modelPlanDetail2,
            'planActionForm' => $planActionForm,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider
        ]);
    }

    public function actionPendingApproval() {

        $searchModel = new InstapPlanPoolSearch();
        $searchModel->setPendingApproval();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'page'=>"pending_approval"
        ]);
    }

    public function actionPendingEditApproval() {

        $searchModel = new UserPlanDetailEditSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('pending-edit-approval', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'page'=>"pending_edit_approval"
        ]);
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    protected function findModel($id)
    {
        if (($model = InstapPlanPool::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException(Yii::t('backend', 'The requested page does not exist.'));
    }
    protected function findModelPlan($id)
    {
        if (($model = UserPlan::findOne(["plan_pool_id" => $id])) !== null) {
            return $model;
        }
        throw new NotFoundHttpException(Yii::t('backend', 'The requested page does not exist.'));
    }

    protected function findModelPlanDetail($id)
    {
        if (($model = UserPlanDetail::findOne(["plan_pool_id" => $id])) !== null) {
            return $model;
        }
        // throw new NotFoundHttpException(Yii::t('backend', 'The requested page does not exist.'));
    }
}
