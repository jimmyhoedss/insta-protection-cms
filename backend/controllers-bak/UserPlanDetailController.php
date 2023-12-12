<?php

namespace backend\controllers;

use Yii;
use common\models\UserPlanDetail;
use common\models\UserPlanDetailEdit;
use common\models\search\UserPlanDetailSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\components\Utility;

/**
 * UserPlanDetailController implements the CRUD actions for UserPlanDetail model.
 */
class UserPlanDetailController extends Controller
{

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                ],
            ],
        ];
    }

    public function actionView($plan_pool_id)
    {
        return $this->render('view', [
            'model' => $this->findModel($plan_pool_id),
        ]);
    }

    public function actionCreate()
    {
        $model = new UserPlanDetail();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }
    // to use to create new row in user_plan_detail_edit model
    public function actionEdit($plan_pool_id) {
        $model = UserPlanDetailEdit::find()->andWhere(['plan_pool_id' => $plan_pool_id])->one();
        if (!$model) {
            $m = $this->findModel($plan_pool_id);
            $model = $m->getNewEditedModel();
        } 
        $model->plan_pool_id = $plan_pool_id;
        
        if ($model->load(Yii::$app->request->post())) {
            if($model->save()){
                Yii::$app->session->setFlash('success', "Plan detail edit request submitted!");
                return $this->redirect(['view', 'plan_pool_id' => $model->plan_pool_id]);
            } else {
                Yii::$app->session->setFlash('error',"Unable to save changes");
            }
        }

        return $this->render('edit', [
            'model' => $model,
        ]);
    }

    public function actionEditApprove($plan_pool_id) {
        $planDetail = UserPlanDetail::find()->where(['plan_pool_id' => $plan_pool_id])->one();
        $planDetailEdit = UserPlanDetailEdit::find()->where(['plan_pool_id' => $plan_pool_id])->one();
        if($planDetailEdit) {

                $planDetail->sp_brand = $planDetailEdit->sp_brand;
                $planDetail->sp_model_number = $planDetailEdit->sp_model_number;
                $planDetail->sp_model_name = $planDetailEdit->sp_model_name;
                $planDetail->sp_serial = $planDetailEdit->sp_serial;
                $planDetail->sp_imei = $planDetailEdit->sp_imei;
                $planDetail->sp_color = $planDetailEdit->sp_color;
                if(!$planDetail->save()) {
                    Yii::$app->session->setFlash('error', 'Unable to update detail');
                    return $this->redirect(['user-plan-detail/view', 'plan_pool_id' => $plan_pool_id]);
                }

        } else {
            throw new NotFoundHttpException(Yii::t('backend', 'No plan detail edit request not exist.'));
        }

        $planDetailEdit->delete();
        Yii::$app->session->setFlash('success', "Plan details edit approved!");
        return $this->redirect(['instap-plan-pool/update', 'id' => $plan_pool_id]);
        // exit();
    }

    public function actionEditReject($plan_pool_id){
        //print_r("reject");
        $planDetailEdit = UserPlanDetailEdit::find()->where(['plan_pool_id' => $plan_pool_id])->one();
        $planDetailEdit->delete();
        Yii::$app->session->setFlash('success', "Plan details edit rejected!");
        return $this->redirect(['instap-plan-pool/update', 'id' => $plan_pool_id]);

    }

    public function actionEditHistory($plan_pool_id)
    {
        $searchModel = new UserPlanDetailEditHistorySearch();
        $searchModel->setPlanPoolId($plan_pool_id);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('edit-history', [
            'dataProvider' => $dataProvider,
        ]);
    }

    protected function findModel($id)
    {
        //if (($model = UserPlanDetail::findOne($id)) !== null) {
        $model = UserPlanDetail::find()->andWhere(['plan_pool_id' => $id])->one();
        if ($model !== null) {
            return $model;
        }
        throw new NotFoundHttpException(Yii::t('backend', 'The requested page does not exist.'));
    }
}
