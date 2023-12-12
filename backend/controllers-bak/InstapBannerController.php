<?php

namespace backend\controllers;

use Yii;
use common\models\InstapPromotion;
use common\models\InstapPromotionLocalization;
use common\models\search\InstapPromotionSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\components\MyLocalization;

/**
 * InstapPromotionController implements the CRUD actions for InstapPromotion model.
 */
class InstapBannerController extends Controller
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

    /**
     * Lists all InstapPromotion models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new InstapPromotionSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single InstapPromotion model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new InstapPromotion model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new InstapPromotion();
        $success = false;
        if ($model->load(Yii::$app->request->post())) {

            $transaction = Yii::$app->db->beginTransaction();
            try{
                if($model->thumbnail == null) {
                    Yii::$app->session->setFlash('error', "Image cannot be empty");
                    return $this->render('create', [
                        'model' => $model,
                    ]);
                } else {
                    $model->save();
                    $i10n = InstapPromotionLocalization::makeModel(MyLocalization::ENGLISH_SINGAPORE,$model);
                    $i10n->save();
                    if(empty($model->hasErrors()) && empty($i10n->hasErrors())) {
                        $success = true;
                    }
                }
            } catch (yii\db\IntegrityException $e) {          
                $str = Utility::jsonifyError("", "database error", CustomHttpException::KEY_UNEXPECTED_ERROR);
                throw new CustomHttpException($str, CustomHttpException::UNPROCESSABLE_ENTITY);
            }
            // save a default en-GB to InstapPlanLocalization
            if($success) {
                $transaction->commit();
                Yii::$app->session->setFlash('success', "Banner created successfully");
                return $this->redirect(['index']);
            } else {
                $transaction->rollback();
                Yii::$app->session->setFlash('error', "Fail to update banner");
            }

        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    protected function findModel($id)
    {
        if (($model = InstapPromotion::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('backend', 'The requested page does not exist.'));
    }
}
