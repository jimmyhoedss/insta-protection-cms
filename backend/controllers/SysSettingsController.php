<?php
namespace backend\controllers;

use Yii;
use Aws\S3\S3Client; 
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use trntv\filekit\widget\Upload;
use common\models\User;
use common\models\SysSettings;
use common\models\SysUserToken;
use common\models\DealerCompany;
use common\models\UserActionHistory;
use common\models\form\LoginForm;
use common\models\form\ForceLogoutForm;
use common\models\form\AccountForm;
use common\components\MyCustomActiveRecord;
use common\components\keyStorage\FormModel;


/**
 * Site controller
 */
class SysSettingsController extends \yii\web\Controller
{
    public $layout = "common";
    //public $layout = false;

    public function behaviors()
    {
        return [
           
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                       // 'actions' => ['index', 'force-logout', 'force-logout-all'],
                       'allow' => true,
                       'roles' => [User::ROLE_ADMINISTRATOR],
                    ],
                    [
                       'actions' => ['otp-list', 'tools'],
                       'allow' => true,
                       'roles' => [User::ROLE_IP_SUPER_ADMINISTRATOR],
                    ],
                ],
            ],
            /**/
        ];
    }


    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ]
        ];
    }
    public function actionTools() {
        return $this->render('tools');
    }

    // force logout either by company or by user
    public function actionForceLogout() {
        $model = new ForceLogoutForm();
        $users = User::find()->active()->all();
        $companies = DealerCompany::find()->active()->all();

        if ($model->load(Yii::$app->request->post())) {
            $dealer_company_id = $model->dealer_company_id;
            $user_id = $model->user_id;

            if(isset($dealer_company_id)) {
                $success = User::forceLogoutByCompany($dealer_company_id);
                if($success) {
                    Yii::$app->session->setFlash('success', Yii::t('backend', 'Force Logout all from company.'));
                    return $this->redirect(['force-logout']);   
                }
            }
            if(isset($user_id)) {
                $success = User::forceLogoutByUserId($model->user_id);
                if($success) {
                    Yii::$app->session->setFlash('success', Yii::t('backend', 'Force Logout user successful.'));
                    return $this->redirect(['force-logout']);
                }
            }
        }
        return $this->render('force-logout', 
            [
                'model' => $model, 
                'user_arr' => $users,
                'company_arr' => $companies
            ]);
    }

    public function actionForceLogoutAll() {
        $success = User::forceLogoutAll();
        if($success) {
            Yii::$app->session->setFlash('success', Yii::t('backend', 'Force Logout All User.'));
            return $this->redirect(['force-logout']);
        }
        return $this->render('force-logout');
    }


    public function actionOtpList() {
        $model = SysUserToken::find()->where(['or', ['type' => SysUserToken::TYPE_ONE_TIME_PASSWORD_API], ['type' => SysUserToken::TYPE_ONE_TIME_PASSWORD_CMS]])->orderBy(['created_at' => SORT_DESC])->all();
        return $this->render('otp-list',['models' => $model]);
    }


}

