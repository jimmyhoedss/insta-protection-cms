<?php

namespace backend\controllers;

use Yii;
use common\models\SysFcmMessage;
use common\models\search\SysFcmMessageSearch;
use common\commands\SendFcmCommand;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * SysFcmMessageController implements the CRUD actions for SysFcmMessage model.
 */
class SysFcmMessageController extends Controller
{
    const TYPE_INDIVIDUAL = "individual";
    const TYPE_GROUP = "group";
    const TYPE_BROADCAST = "broadcast";

    const ACTION_INBOX = "inbox";
    const ACTION_INBOX_SILENT = "inbox_silent";    
    const ACTION_SYSTEM = "system";
    const ACTION_FORCE_LOGOUT = "force_logout"; 
    const ACTION_FORCE_LOGOUT_SILENT = "force_logout_silent"; 
    const ACTION_DO_DAILY_RESYNC = "daily_resync";

    const BROADCAST_ID = "/topics/system";


    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                       //'actions' => ['index'],
                       'allow' => true,
                       //'roles' => [User::ROLE_ADMINISTRATOR],
                       'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionLoytest() {
        return Yii::$app->commandBus->handle(new SendFcmCommand([            
            'model' => $this,
        ]));
    }

    /**
     * Lists all SysFcmMessage models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new SysFcmMessageSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single SysFcmMessage model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new SysFcmMessage model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new SysFcmMessage();

        /*
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }
        return $this->render('create', [
            'model' => $model,
        ]);
        */
        /*
        if (($u = UserProfile::findOne($user_id)) == null) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        */

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->sendFcm()) {
            Yii::$app->session->setFlash('success', "Message Sent.");
        }

        return $this->render('create', [
            'model' => $model,
        ]);


    }

    /**
     * Updates an existing SysFcmMessage model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
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
     * Deletes an existing SysFcmMessage model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the SysFcmMessage model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return SysFcmMessage the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = SysFcmMessage::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
