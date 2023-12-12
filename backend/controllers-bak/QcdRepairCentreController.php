<?php

namespace backend\controllers;

use Yii;
use common\models\QcdRepairCentre;
use common\models\QcdDeviceMakerRepairCentre;
use common\models\form\QcdRepairCentreForm;
use common\models\search\QcdRepairCentreSearch;
use common\components\MyCustomActiveRecord;

use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

class QcdRepairCentreController extends Controller
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
        $searchModel = new QcdRepairCentreSearch();
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
        $success = false;
        $model = new QcdRepairCentre();
        $repairCentreForm = new QcdRepairCentreForm();

        if ($model->load(Yii::$app->request->post()) && $repairCentreForm->load(Yii::$app->request->post()) && $model->save()) {
             $transaction = Yii::$app->db->beginTransaction();
            try {
                $repair_centre_id = $model->id;            
                if(!empty($repairCentreForm->brand_id_arr)) {
                    $device_maker_ids = array_map('intval', $repairCentreForm->brand_id_arr);
                    foreach ($device_maker_ids as $device_maker_id) {
                        $m = QcdDeviceMakerRepairCentre::makeModel($device_maker_id, $repair_centre_id);
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
            'repairCentreForm' => $repairCentreForm
        ]);
    }

    public function actionUpdate($id)
    {
        $success = false;
        $model = $this->findModel($id);
        $repairCentreForm = new QcdRepairCentreForm();
        $brands = QcdDeviceMakerRepairCentre::find()->where(['repair_centre_id' =>$model->id])->asArray()->all();
        $brand_arr = array_column($brands, 'device_maker_id');
        $repairCentreForm->brand_id_arr  = $brand_arr;

        if ($model->load(Yii::$app->request->post()) && $repairCentreForm->load(Yii::$app->request->post()) && $model->save()) {
             $transaction = Yii::$app->db->beginTransaction();
            try {
                QcdDeviceMakerRepairCentre::deleteAll('repair_centre_id = :repair_centre_id', [':repair_centre_id' => $model->id]);                
                $repair_centre_id = $model->id;            
                if(!empty($repairCentreForm->brand_id_arr)) {
                    $device_maker_ids = array_map('intval', $repairCentreForm->brand_id_arr);
                    foreach ($device_maker_ids as $device_maker_id) {
                        $m = QcdDeviceMakerRepairCentre::makeModel($device_maker_id, $repair_centre_id);
                        $m->save();
                    }
                    // $model->validate();
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
            'repairCentreForm' => $repairCentreForm
        ]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->status = MyCustomActiveRecord::STATUS_DISABLED;
        $model->save();
        // $transaction = Yii::$app->db->beginTransaction();
        // try {
        //     if($model->delete()) {
        //         QcdDeviceMakerRepairCentre::deleteAll('repair_centre_id = :repair_centre_id', [':repair_centre_id' => $model->id]);
        //         $success = true;
        //     }
                
        // }catch (yii\db\IntegrityException $e) {
        //         $transaction->rollback();
        //     }

        // if($success) {
        //     $transaction->commit();
        //     Yii::$app->session->setFlash('success', "Delete successful!");
        // }else {
        //         $transaction->rollback();
        //         Yii::$app->session->setFlash('error', "Delete fail!");
        //     }

        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        if (($model = QcdRepairCentre::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('backend', 'The requested page does not exist.'));
    }
}
