<?php

namespace backend\controllers;

use Yii;
use common\models\User;
use common\models\form\LoginForm;
use common\models\InstapPlanPool;
use common\models\search\UserSearch;
use common\models\search\InstapPlanPoolSearch;
use common\models\form\IpStaffForm;

use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use api\components\CustomHttpException;



class UserController extends Controller
{


    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                       //'actions' => ['index'],
                       'allow' => '@',
                    ],
                ],
            ],
        ];
    }


    public function actionIndex()
    {
        $searchModel = new UserSearch();
        $searchModel->email_status = User::EMAIL_STATUS_VERIFIED;
        $searchModel->mobile_status = User::MOBILE_STATUS_VERIFIED;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        // Yii::$app->language = 'zh-ZH'; 
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'page'=>"index"
        ]);
    }

    public function actionDisabled()
    {
        $searchModel = new UserSearch();
        $searchModel->searchDisableMode();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        //return $this->render('disabled', [
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'page' => 'disabled'

        ]);
    }


    public function actionView($id)
    {
        $searchModel = new InstapPlanPoolSearch();
        $searchModel->setUserId($id);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        // $user_plan = InstapPlanPool::find()->andWhere(['user_id'=>$id])->one();
        return $this->render('view', [
            'model' => $this->findModel($id),
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            if($model->save()){
                if($model->account_status == User::ACCOUNT_STATUS_SUSPENDED) {
                    User::forceLogoutByUserId($model->id);
                }
                Yii::$app->session->setFlash('success', "Updated successfully!");
                return $this->redirect('index');
            } else {
                Yii::$app->session->setFlash('error', Yii::t('backend', 'Fail to update.'));
            }
        }
        return $this->render('update', [
            'model' => $model,
        ]);
    }


    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('backend', 'The requested page does not exist.'));
    }

    public function actionIpStaff()
    {
        $searchModel = new UserSearch();
        $searchModel->searchIpStaffMode(true);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'page'=>"ip_staff"
        ]);
    }

    public function actionCreateIpStaff()
    {
        // $model = new User();
        $modelIpStaff = new IpStaffForm();
        $modelIpStaff->scenario = IpStaffForm::SCENARIO_ASSIGN_IP_STAFF;

        if ($modelIpStaff->load(Yii::$app->request->post())) {

            $transaction = Yii::$app->db->beginTransaction();
            try{
                
                $auth = Yii::$app->authManager;
                $userId = $modelIpStaff->user_id;
                $user = User::findOne(['id' => $userId]);

                $user->setPassword($modelIpStaff->password);
                $user->email_admin = $modelIpStaff->email_admin;
                $r = $modelIpStaff->role_arr;
                $p = $modelIpStaff->permission_arr;
                if($user->save() && $this->assignIpRole($auth, $userId, $r, $p)) {
                    $transaction->commit();
                    Yii::$app->session->setFlash('success', "Updated successfully!");
                    return $this->redirect('ip-staff');
                }
                // User::find()->andWhere(['id'=>$userId])->one()->updateAttributes(['notes'=>$modelIpStaff->notes]);

            } catch (yii\db\IntegrityException $e) {
                $transaction->rollback();
                Yii::$app->session->setFlash('error', Yii::t('backend', 'Fail to update.'));
            }
        }

        return $this->render('ip_staff_create', [
            'modelIpStaff' =>$modelIpStaff
        ]);
    }

    public function actionUpdateIpStaff($id)
    {
        $model = $this->findModel($id);
        $modelIpStaff = new IpStaffForm();
        $userId = $model->id;
        $modelIpStaff->email_admin = $model->email_admin;
        $modelIpStaff->password = $model->password_hash;
        $auth = Yii::$app->authManager;

        if ($modelIpStaff->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();
            try{
                //delete all related roles & permission
                $r = User::ipStaffRoles();
                foreach ($r as $rkey => $rvalue) {
                    $role = $auth->getRole($rkey);
                    $auth->revoke($role, $userId);
                }
                $p = User::countryAccessPermissions();
                foreach ($p as $key => $value) {
                    $permission = $auth->getPermission($key);
                    $auth->revoke($permission, $userId);
                }
                //assign back roles & permission
                $model->setPassword($modelIpStaff->password);
                $model->email_admin = $modelIpStaff->email_admin;
                $r = $modelIpStaff->role_arr;
                $p = $modelIpStaff->permission_arr;
                if($model->save() &&  $this->assignIpRole($auth, $userId, $r, $p)) {
                    $transaction->commit();
                    Yii::$app->session->setFlash('success', "Update staff successfully!");
                }

            } catch (yii\db\IntegrityException $e) {
                $transaction->rollback();
                Yii::$app->session->setFlash('error', Yii::t('backend', 'Fail to update.'));
            }
        }

        $roles = $auth->getRolesByUser($userId);
        $permissions = $auth->getPermissionsByUser($userId);
        $modelIpStaff->role_arr = array_keys($roles);
        $modelIpStaff->permission_arr = array_keys($permissions);

        return $this->render('ip_staff_update', [
            'model' => $model,
            'modelIpStaff' => $modelIpStaff
        ]);

    }

    public function actionDeleteIpStaff($id)
    {
        $model = $this->findModel($id);
        $auth = Yii::$app->authManager;
        $userId = $model->id;
        //set email admin to empty and password to empty
        $model->email_admin = "";
        $model->password_hash = "";
        $transaction = Yii::$app->db->beginTransaction();
            try{
                //delete all related roles & permission
                $r = User::ipStaffRoles();
                foreach ($r as $rkey => $rvalue) {
                    $role = $auth->getRole($rkey);
                    $auth->revoke($role, $userId);
                }
                $p = User::countryAccessPermissions();
                foreach ($p as $key => $value) {
                    $permission = $auth->getPermission($key);
                    $auth->revoke($permission, $userId);
                }
                $model->save();
                $transaction->commit();
                Yii::$app->session->setFlash('success', "Delete staff successfully!");

            } catch (yii\db\IntegrityException $e) {
                $transaction->rollback();
                Yii::$app->session->setFlash('error', Yii::t('backend', 'Fail to delete staff.'));
            }

        return $this->redirect('ip-staff');
        

    }

    public function assignIpRole($auth, $userId, $role_arr = null, $permission_arr = null) {
        if(!empty($role_arr)) {
            foreach ($role_arr as $value) {
                $role = $auth->getRole($value);
                $auth->assign($role, $userId);
            }
        }
        if(!empty($permission_arr)) {
            foreach ($permission_arr as $value) {
                $permission = $auth->getPermission($value);
                $auth->assign($permission, $userId);
            }
        }
        return true;
    }

    public function actionUpdateAccount() {
        $model = new LoginForm();
        $user = Yii::$app->user->identity;
        $model->scenario = LoginForm::EMAIL_LOGIN;
        $model->email_admin = $user->email_admin;
        $model->password = $user->password_hash;
        if ($model->load(Yii::$app->request->post())) {
            $user->email_admin = $model->email_admin;
            $user->setPassword($model->password);
            if($user->save()) {
                Yii::$app->session->setFlash('success', Yii::t('backend', 'Update successful.'));
            } else {
                $msg = "";
                if ($user->hasErrors()){
                    foreach ($user->getFirstErrors() as $name => $error) {
                        $msg .= $error . "<br>";
                    }
                }
                Yii::$app->session->setFlash('error', $msg);
            }

            // print_r($user->getErrors());exit();
        }

        return $this->render('account', [
            'model' => $model
        ]);
    }

    public function actionBirthdayMonth() {
        // print_r("expression");exit();
        //get birth of current month
        $searchModel = new UserSearch();
        $searchModel->searchBirthdayOfMonth();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('birthday_list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'page'=>"index"

        ]);
    }
}
