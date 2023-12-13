<?php

namespace backend\controllers;

use Yii;
use common\models\QcdRetailStore;
use common\models\QcdDeviceMakerRetailStore;
use common\models\QcdInstapPlanRetailStore;
use common\models\form\QcdRetailStoreForm;
use common\models\search\QcdRetailStoreSearch;
use common\components\MyCustomActiveRecord;

use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

class QcdRetailStoreController extends Controller
{
 
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel = new QcdRetailStoreSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionCreate()
    {
        $success = false;
        $model = new QcdRetailStore();
        $retailStoreForm = new QcdRetailStoreForm();

        if ($model->load(Yii::$app->request->post()) && $retailStoreForm->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $retail_store_id = $model->id;            
                if(!empty($retailStoreForm->brand_id_arr)) {
                    $device_maker_ids = array_map('intval', $retailStoreForm->brand_id_arr);
                    foreach ($device_maker_ids as $device_maker_id) {
                        $m = QcdDeviceMakerRetailStore::makeModel($device_maker_id, $retail_store_id);
                        $m->save();
                    }
                }

                if(!empty($retailStoreForm->plan_id_arr)) {
                    $instap_plan_ids = array_map('intval', $retailStoreForm->plan_id_arr);
                    foreach ($instap_plan_ids as $instap_plan_id) {
                        $m = QcdInstapPlanRetailStore::makeModel($instap_plan_id, $retail_store_id);
                        $m->save();
                    }
                }

                $success = true;
                
            }catch (yii\db\IntegrityException $e) {
                $transaction->rollback();
            }

            if($success) {
                $transaction->commit();
                Yii::$app->session->setFlash('success', "Save successful!");
            }else {
                 $transaction->rollback();
                 Yii::$app->session->setFlash('error', "Fail to save!");
            }
            return $this->redirect(['index']);

        }

        return $this->render('create', [
            'model' => $model,
            'retailStoreForm' => $retailStoreForm
        ]);
    }

    public function actionUpdate($id)
    {

        $success = false;
        $model = $this->findModel($id);
        $retailStoreForm = new QcdRetailStoreForm();

        $brands = QcdDeviceMakerRetailStore::find()->where(['retail_store_id' => $model->id])->asArray()->all();
        $brand_arr = array_column($brands, 'device_maker_id');
        $retailStoreForm->brand_id_arr  = $brand_arr;

        $plans = QcdInstapPlanRetailStore::find()->where(['retail_store_id' => $model->id])->asArray()->all();
        $plan_arr = array_column($plans, 'instap_plan_id');
        $retailStoreForm->plan_id_arr  = $plan_arr;

        if ($model->load(Yii::$app->request->post()) && $retailStoreForm->load(Yii::$app->request->post()) && $model->save()) {
             $transaction = Yii::$app->db->beginTransaction();
            try {
                QcdDeviceMakerRetailStore::deleteAll('retail_store_id = :retail_store_id', [':retail_store_id' => $model->id]);                
                $retail_store_id = $model->id;            
                if(!empty($retailStoreForm->brand_id_arr)) {
                    $device_maker_ids = array_map('intval', $retailStoreForm->brand_id_arr);
                    foreach ($device_maker_ids as $device_maker_id) {
                        $m = QcdDeviceMakerRetailStore::makeModel($device_maker_id, $retail_store_id);
                        $m->save();
                    }
                    // $model->validate();
                }

                QcdInstapPlanRetailStore::deleteAll('retail_store_id = :retail_store_id', [':retail_store_id' => $model->id]);                
                if(!empty($retailStoreForm->plan_id_arr)) {
                    $instap_plan_ids = array_map('intval', $retailStoreForm->plan_id_arr);
                    foreach ($instap_plan_ids as $instap_plan_id) {
                        $m = QcdInstapPlanRetailStore::makeModel($instap_plan_id, $retail_store_id);
                        $m->save();
                    }
                }

                $success = true;
                
            }catch (yii\db\IntegrityException $e) {
                $transaction->rollback();
            }

            if($success) {
                $transaction->commit();
                Yii::$app->session->setFlash('success', "Save successful!");
            }else {
                 $transaction->rollback();
                 Yii::$app->session->setFlash('error', "Save fail!");
            }
            return $this->redirect(['index']);

        }

        return $this->render('update', [
            'model' => $model,
            'brands' => $brand_arr,
            'retailStoreForm' => $retailStoreForm
        ]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->status = MyCustomActiveRecord::STATUS_DISABLED;
        $model->save();

        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        if (($model = QcdRetailStore::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('backend', 'The requested page does not exist.'));
    }
}
