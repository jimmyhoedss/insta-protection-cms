<?php

namespace backend\controllers;

use Yii;
use common\models\User;
use common\models\UserActionHistory;
use common\models\SysAuditTrail;
use common\models\search\UserActionHistorySearch;
use common\models\search\SysFcmMessageSearch;
use common\models\search\SysAuditTrailSearch;

use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * UserActionHistoryController implements the CRUD actions for UserActionHistory model.
 */
class ActivityLogController extends Controller
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
    public function actionAuditTrailLog()
    {
        $searchModel = new SysAuditTrailSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination->pageSize=50;

        return $this->render('audit-trail-log', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    public function actionAuditTrailLogDetail($id)
    {
        if (($model = SysAuditTrail::findOne($id)) == null) {
            throw new NotFoundHttpException('The requested page does not exist.');    
        }        

        return $this->render('audit-trail-log-detail', [
            'model' => $model,
        ]);
    }

    /*
    public function actionUserActionLog()
    {
        $searchModel = new UserActionHistorySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('user-action-log', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    public function actionPushMessageLog()
    {
        $searchModel = new SysFcmMessageSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('push-message-log', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    */


}
