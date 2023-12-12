<?php

namespace backend\controllers;

use Yii;
use common\models\User;
use common\models\UserProfile;
use common\models\SysFcmGroupUser;
use common\models\search\SysFcmGroupUserSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * SysFcmGroupUserController implements the CRUD actions for SysFcmGroupUser model.
 */
class SysFcmGroupUserController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
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
     * Deletes an existing SysFcmGroupUser model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id , $fcm_group_id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['sys-fcm-group/view', 'id' => $fcm_group_id]);
    }

    public function actionAddToGroup($user_id) {
        if (($u = UserProfile::findOne($user_id)) == null) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        $model = new SysFcmGroupUser();

        if ($model->load(Yii::$app->request->post()) && $model->addToGroup($user_id)) {
            Yii::$app->session->setFlash('success', "Added To Selected Group.");                 
            return $this->redirect(['sys-fcm-group/view', 'id' => $model->fcmGroup->id]);
        } 
        if ($model->load(Yii::$app->request->post()) && !$model->addToGroup($user_id)) {            
            Yii::$app->session->setFlash('error', "Already In Selected Group.");         
        }

        return $this->render('add-to-group', [
            'model' => $model,
            'userProfile' => $u
        ]);
    }

    /**
     * Finds the SysFcmGroupUser model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return SysFcmGroupUser the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = SysFcmGroupUser::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
