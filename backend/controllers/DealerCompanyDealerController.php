<?php

namespace backend\controllers;

use Yii;
use common\models\DealerCompanyDealer;
use common\models\DealerCompany;
use common\models\User;
use common\models\search\DealerCompanyDealerSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;


/**
 * DealerCompanyDealerController implements the CRUD actions for DealerCompanyDealer model.
 */
class DealerCompanyDealerController extends Controller
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
                       'roles' => [User::ROLE_IP_ADMINISTRATOR, User::ROLE_IP_MANAGER],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all DealerCompanyDealer models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new DealerCompanyDealerSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single DealerCompanyDealer model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {   
        // $this->layout = "chart";
        // $this->layout = false;
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new DealerCompanyDealer model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new DealerCompanyDealer();
        $success = false;
        if ($model->load(Yii::$app->request->post())) {
            
            try {

                $upline = $model->dealer_company_upline_id;
                $downline = $model->dealer_company_downline_id;
                $hierarchy = $model->find()->asArray()->all();
                $exist = DealerCompanyDealer::find()->andWhere(['dealer_company_downline_id' => $downline])->one();
                $a = $this->find_pat($hierarchy, $upline);

                //check for all the company upline, prevent from adding own upline
                foreach ($a as $r) {
                    // print_r($downline);
                    if ($r['dealer_company_upline_id'] == $downline){
                        // print_r("knot add ur upline");
                         Yii::$app->session->setFlash('error', "Unable to add this company as downline!");
                         return $this->redirect(['create']);
                    }
                }
                if(($upline === $downline)) {
                    Yii::$app->session->setFlash('error', "Invalid downline assignment!");
                    return $this->redirect(['create']);
                }

                if($exist) {
                    Yii::$app->session->setFlash('error', "Already exist under another upline!");
                    return $this->redirect(['create']);
                }

                if($model->save()) {
                    $company = DealerCompany::find()->where(['id'=>$downline])->one();
                    Yii::$app->session->setFlash('success', "Downline ". $company->business_name." was added!");
                     return $this->redirect(['dealer-company/chart', 'id' => $upline]);
                }

            } catch (\yii\db\IntegrityException $e) {
                Yii::$app->session->setFlash('error', "Invalid attempt to insert duplicate downline");
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    //find dealer upline
    public function find_pat($a, $n){
        $out = array();
        foreach ($a as $r){
            if ($r['dealer_company_downline_id'] == $n){
                $out = $this->find_pat($a, $r['dealer_company_upline_id']);
                $out[]=$r;
            }
        }
        return $out;
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            if($model->validate() && $model->save()) {
                return $this->redirect(['index']);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        if (($model = DealerCompanyDealer::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('backend', 'The requested page does not exist.'));
    }
}
