<?php

namespace backend\controllers;

use Yii;
use common\models\SysFeedback;
use common\models\search\SysFeedbackSearch;
use common\components\MyCustomActiveRecord;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use common\models\User;

/**
 * SysFeedbackController implements the CRUD actions for SysFeedback model.
 */
class SysFeedbackController extends Controller
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
     * Lists all SysFeedback models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new SysFeedbackSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $post = Yii::$app->request->post('SysFeedback');

        if ($post != null) {            
            $model->notes = $post['notes'];
            $model->status = $post['status'];
            if ($model->save()) {
              return $this->redirect(['index']);  
            }
        }
        /*
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        }
        */
        return $this->render('update', [
            'model' => $model,
        ]);
    }


    public function actionDelete($id)
    {
        //soft delete
        //$this->findModel($id)->delete();
        $m = $this->findModel($id);
        $m->updateAttributes(['status'=> MyCustomActiveRecord::STATUS_DISABLED]);

        return $this->redirect(['index']);
    }

    /**
     * Finds the SysFeedback model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return SysFeedback the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = SysFeedback::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
