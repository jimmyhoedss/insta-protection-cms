<?php

namespace backend\controllers;

use Yii;
use common\models\User;
use common\models\SysFcmTokenHistory;
use common\models\search\SysFcmTokenHistorySearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;

/**
 * SysFcmTokenHistoryController implements the CRUD actions for SysFcmTokenHistory model.
 */
class SysFcmTokenHistoryController extends Controller
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
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                       //'actions' => ['index'],
                       'allow' => true,
                       'roles' => [User::ROLE_ADMINISTRATOR, User::ROLE_IP_ADMINISTRATOR, User::ROLE_IP_MANAGER, User::ROLE_IP_ADMIN_ASSISTANT],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all SysFcmTokenHistory models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new SysFcmTokenHistorySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        //$this->layout = false;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
            
    }
    public function actionHistory()
    {


        $dataProvider = new ActiveDataProvider([
            'query' => SysFcmTokenHistory::find(),
        ]);
        //$this->layout = false;

        return $this->render('history', [
            //'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
            
    }
    /**
     * Displays a single SysFcmTokenHistory model.
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
     * Creates a new SysFcmTokenHistory model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new SysFcmTokenHistory();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing SysFcmTokenHistory model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing SysFcmTokenHistory model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the SysFcmTokenHistory model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return SysFcmTokenHistory the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = SysFcmTokenHistory::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
