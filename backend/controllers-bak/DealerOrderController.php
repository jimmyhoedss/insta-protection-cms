<?php

namespace backend\controllers;

use Yii;
use common\models\DealerOrder;
use common\models\search\DealerOrderSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * DealerOrderController implements the CRUD actions for DealerOrder model.
 */
class DealerOrderController extends Controller
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
     * Lists all DealerOrder models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new DealerOrderSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single DealerOrder model.
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

    public function actionChart()
    {
        $data = [];
        date_default_timezone_set("Asia/Singapore");
        $months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        for($i=0; $i<count($months); $i++) {
            $start = strtotime( $months[$i].date("Y"));
            $end = strtotime( $months[$i].date("Y")."+1 month"."-1 second");
         
            $sales = DealerOrder::find()->where(['between', 'created_at', $start, $end ])->count();
         
            array_push($data, $sales);
        }
        return $this->render('chart',[
            'data' =>$data
        ]);
    }

    /**
     * Creates a new DealerOrder model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new DealerOrder();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing DealerOrder model.
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

    protected function findModel($id)
    {
        if (($model = DealerOrder::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('backend', 'The requested page does not exist.'));
    }
}
