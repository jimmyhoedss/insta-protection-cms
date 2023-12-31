<?php

namespace backend\controllers;

use Yii;
use common\models\DealerCompany;
use common\models\InstapPlan;
use common\models\search\InstapPlanSearch;
use common\models\InstapPlanPool;
use common\models\InstapPlanLocalization;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\components\MyLocalization;
use common\components\Utility;
use api\components\CustomHttpException;

/**
 * InstapPlanController implements the CRUD actions for InstapPlan model.
 */
class InstapPlanController extends Controller
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
        ];
    }

    public function actionIndex()
    {
        $searchModel = new InstapPlanSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionCreate()
    {
        $model = new InstapPlan();
        $success = false;
        if ($model->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();
            try{
                
                $model->save();
                $i10n = InstapPlanLocalization::makeModel(MyLocalization::ENGLISH_SINGAPORE,$model);
                $i10n->save();
                if(empty($model->hasErrors()) && empty($i10n->hasErrors())) {
                    $success = true;
                }
               
            } catch (yii\db\IntegrityException $e) {          
                $str = Utility::jsonifyError($e->getName(), "database error", CustomHttpException::KEY_UNEXPECTED_ERROR);
                throw new CustomHttpException($str, CustomHttpException::UNPROCESSABLE_ENTITY);
            }
            // save a default en-GB to InstapPlanLocalization
            if($success) {
                $transaction->commit();
                Yii::$app->session->setFlash('success', "Plan created successfully");
                return $this->redirect(['index']);
            } else {
                $transaction->rollback();
                Yii::$app->session->setFlash('error', "Fail to update plan");
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $modelLocalisation = $this->findModelLocalisation($model->id);
        
        if ($model->load(Yii::$app->request->post())) {
            $modelLocalisation->name = $model->name;
            $modelLocalisation->description = $model->description;
            if($model->thumbnail == null) {
                Yii::$app->session->setFlash('error', "Image cannot be empty");
            } else {
                $model->save();
                $modelLocalisation->save();
                Yii::$app->session->setFlash('success', "Plan update successfully");
                return $this->redirect(['index']);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionOrder($id)
    {
        $success = true;
        $transaction = Yii::$app->db->beginTransaction();

        try {
            //for ($i=0; $i<1000; $i++) {
                $m = InstapPlanPool::makeModel($id,1);
                if (!$m->save()) {
                   $success = false;
                   //break;
                };
            //}

        } catch (yii\db\IntegrityException $e) {
            $success = false;
        }

        //print_r($m->errors);
        //exit();        

        if ($success) {
            $transaction->commit();
            return $this->redirect(['instap-plan-pool/index']);
        } else {
            $transaction->rollback();
            throw CustomHttpException::internalServerError("Error creating order.");
        }

    }

    protected function findModel($id)
    {
        if (($model = InstapPlan::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('backend', 'The requested page does not exist.'));
    }

    protected function findModelLocalisation($plan_id)
    {
        if (($model = InstapPlanLocalization::find()->where(['plan_id' => $plan_id])->one()) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('backend', 'The requested page does not exist.'));
    }
}
