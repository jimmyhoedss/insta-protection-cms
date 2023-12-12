<?php

namespace dashboard\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use common\models\User;
use common\models\UserCase;
use common\models\UserCaseAction;
use common\models\InstapPlanPool;
use common\models\QcdDeviceMaker;
use common\models\SysRegion;
use common\models\search\UserSearch;
use common\models\search\InstapPlanPoolSearch;
use common\models\search\UserPlanActionSearch;
use common\models\form\RegisterClaimForm;
use common\models\form\RegistrationResubmitClaimForm;
use api\components\CustomHttpException;
use Faker\Provider\DateTime;

/**
 * MainController implements the CRUD actions for User model.
 */
class PolicyController extends Controller
{
    public function behaviors(){
        return [           
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                       'allow' => true,
                       'roles' => ['@'],
                    ],
                ],
            ]
        ];
    }

    public function actions(){
        return [
            'set-locale' => [
                'class' => 'common\actions\SetLocaleAction',
                'locales' => Yii::$app->params['availableLocales'],
                'localeCookieName'=>'_locale',
            ]
        ];
    }

    public function actionIndex(){
        $searchModel = new InstapPlanPoolSearch();
        $searchModel->setUserId(Yii::$app->user->id);
        $searchModel->claim_plans = true; //display claimable plans
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $user = User::find(["id"=>Yii::$app->user])->one();

        return $this->render('index', [
            'user' => $user,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    // public function actionView($id){
    //     $model = $this->findModel($id);
    //     if (!Yii::$app->user->can('editOwnModel', ['model' => $model, 'attribute'=>'user_id'])) {
    //         throw new NotFoundHttpException(Yii::t('backend', 'The requested page does not exist.'));
    //     }
    //     $notes = "";
    //     if($model->userCase){
    //         $action = UserCaseAction::find()->where(['case_id' => $model->userCase->id])->orderBy(['created_at'=>SORT_DESC])->one();
    //         // $action = UserCaseAction::find()->where(['case_id' => $model->userCase->id])->andWhere(['in', 'action_status', [UserCaseAction::ACTION_CLAIM_REQUIRE_CLARIFICATION, UserCaseAction::ACTION_CLAIM_CLOSED, UserCaseAction::ACTION_CLAIM_CANCELLED , UserCaseAction::ACTION_CLAIM_REJECTED]])->orderBy(['created_at'=>SORT_DESC])->one();
    //         $notes = $action ? $action->notes_user : "";
    //     }

    //     $searchModel = new UserPlanActionSearch();
    //     $searchModel->setPlanPoolId($id);
    //     $searchModel->setMergeCaseAction();
    //     $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

    //     return $this->render('view', [
    //         'model' => $model,
    //         'searchModel' => $searchModel,
    //         'dataProvider' => $dataProvider,
    //         'notes' => $notes,
    //     ]);
    // }

    public function actionClaim($id){
        $model = $this->findModel($id);
        if (!Yii::$app->user->can('editOwnModel', ['model' => $model, 'attribute'=>'user_id'])) {
            throw new NotFoundHttpException(Yii::t('dashboard', 'The requested page does not exist.'));
        }
        if($model->plan_status != InstapPlanPool::STATUS_ACTIVE) {
            Yii::$app->session->setFlash('error', Yii::t('dashboard', "Only can proceed to claim when plan is active!"));
            return $this->redirect(['index']);
            
            // return $this->redirect(['view', 'id' => $id]);
        }
        $form = new RegisterClaimForm();
        $form->plan_pool_id = $id;
        $form->scenario = RegisterClaimForm::SCENARIO_CMS_FORM;

        $modelBrand = QcdDeviceMaker::find()->where(['LOWER(device_maker)' => strtolower($model->userPlan->details->sp_brand)])->one();

        if ($form->load(Yii::$app->request->post())) {
            $occurred_at = strtotime($form->occurred_at);
            $this->uploadPhoto($form, 'RegisterClaimForm');

            $form->occurred_at = $occurred_at;
            if ($form->validate() && $case = $form->registerClaim()) {
                if ($case) {
                    Yii::$app->session->setFlash('success', Yii::t('dashboard', "Claim submitted!"));
                } else {
                    Yii::$app->session->setFlash('error', Yii::t('dashboard', "Claim submit fail!"));
                }
                return $this->redirect(['index']);

                // return $this->redirect(['view', 'id' => $id]);
            }
            $form->occurred_at = date('Y-m-d H:i:s', $form->occurred_at);
        }
        return $this->render('claim', [
            'm' => $form,
            'model' => $model,
            'modelBrand' => $modelBrand,
            'terms_url' => $this->getTermsUrl(),
        ]);
    }

    public function actionClarification($id){
        $model = $this->findModel($id);
        if (!Yii::$app->user->can('editOwnModel', ['model' => $model, 'attribute'=>'user_id'])) {
            throw new NotFoundHttpException(Yii::t('dashboard', 'The requested page does not exist.'));
        }
        if($model->plan_status != InstapPlanPool::STATUS_PENDING_CLAIM || $model->userCase->current_case_status != UserCase::CASE_STATUS_CLAIM_REQUIRE_CLARIFICATION) {
            Yii::$app->session->setFlash('error', Yii::t('dashboard', "Only can proceed when claim requires clarification!"));
            return $this->redirect(['index']);
            
        }
        $notes = "";
        if($model->userCase){
            $action = UserCaseAction::find()->where(['case_id' => $model->userCase->id])->andWhere(['action_status' => UserCaseAction::ACTION_CLAIM_REQUIRE_CLARIFICATION])->orderBy(['created_at'=>SORT_DESC])->one();
            $notes = $action ? $action->notes_user : "";
        }

        $form = new RegistrationResubmitClaimForm();
        $form->plan_pool_id = $id;

        if ($form->load(Yii::$app->request->post())) {
            $this->uploadPhoto($form, 'RegistrationResubmitClaimForm');

            $scenario=null;
            if (!$form->description && !$form->image_file) {
                Yii::$app->session->setFlash('error', Yii::t('dashboard', "Only can proceed when claim requires clarification!"));
            } else {
                if ($form->description) {
                    $form->scenario = RegistrationResubmitClaimForm::SCENARIO_DETAIL;
                }
                if ($form->image_file) {
                    $form->scenario = RegistrationResubmitClaimForm::SCENARIO_PHOTO;
                }
                if ($form->description && $form->image_file) {
                    $form->scenario = RegistrationResubmitClaimForm::SCENARIO_BOTH;
                }
            }

            if ($form->validate() && $case = $form->resubmit()) {
                if ($case) {
                    Yii::$app->session->setFlash('success', Yii::t('dashboard', "Claim submitted!"));
                } else {
                    Yii::$app->session->setFlash('error', Yii::t('dashboard', "Claim submit fail!"));
                }
                return $this->redirect(['index']);
            }
        }
        return $this->render('clarification', [
            'm' => $form,
            'model' => $model,
            'notes' => $notes,
        ]);
    }

    private function uploadPhoto($form, $form_name){
        $files = [];
        $files['image_file'] = [];
        foreach ($_FILES[$form_name] as $key => $value) {
            $files['image_file'][$key] = $value['image_file'];
        }
        $_FILES = $files;
        $form->image_file = UploadedFile::getInstancesByName("image_file");
    }

    protected function findModel($id){
        if (($model = InstapPlanPool::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('dashboard', 'The requested page does not exist.'));
    }


    private function getTermsUrl() {
        $path = "/terms";
        $region_id = Yii::$app->user->identity->region_id;

        if($region_id == SysRegion::SINGAPORE) {
            $path = "/terms";
        } else if ($region_id == SysRegion::MALAYSIA) {
            $path = "/my/terms";

        } else if ($region_id == SysRegion::VIETNAM) {
            $path = "vn/terms";

        } else if ($region_id == SysRegion::THAILAND) {
            $path = "th/terms";
        } else if($region_id == SysRegion::INDONESIA){
            $path = "id/terms";
        } 
        return Yii::$app->urlManagerFrontend->createUrl($path);
    }
}
