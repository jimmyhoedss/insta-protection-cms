<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use Aws\S3\S3Client; 
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use trntv\filekit\widget\Upload;
use common\models\User;
use common\models\SysSettings;
use common\models\DealerCompany;
use common\models\UserActionHistory;
use common\models\SysUserToken;
use common\models\form\LoginForm;
use common\models\form\ForceLogoutForm;
use common\models\form\AccountForm;
use common\components\MyCustomActiveRecord;
use common\components\keyStorage\FormModel;
use common\models\uatScript\UatClearDB;
use common\models\uatScript\UserTest;
use common\models\uatScript\DealerTest;



/**
 * UserLocationController implements the CRUD actions for UserLocation model.
 */
class ResetDbController extends Controller
{
    public $layout = 'common';

    /** @inheritdoc */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all UserLocation models.
     * @return mixed
     */
    public function actionIndex()
    {   

        return $this->render('index');
    }

    public function actionTest()
    {   
        print_r("123");
    }

    public function actionResetDb()
    {   
       $db = UatClearDB::resetDb();
       if($db) {
        Yii::$app->session->setFlash('success', "Database reset to default!");
         return $this->redirect(['site/login']);
         // return Yii::$app->user->loginUrl;
       }
    }


    // public function actionGo() {
    //    UatClearDB::createCompanys();
    //    UatClearDB::createDealerUsers();
    //    // UatClearDB::createPlans();
    //    UatClearDB::createCompanyRelation();
    //    // UatClearDB::createPlanBanners();
    //     // $src = "https://s3-ap-southeast-1.amazonaws.com/storage.instaprotection/media/plan/1/bXlcCMBrnKNQdq6kg9hNuJzQygLBYQDX.jpg";
    //     // $image = imagecreatefrompng(file_get_contents($src));
    //     // echo $image;
    // }

    // public function actionDealerTest() {
    //     // UserTest::registerTest();
    //     DealerTest::addAssociateTest();
    // }

    // public function actionClearDb() {
    //     //Turn on output buffering
    //     ob_start();
    //     try
    //     {
    //         $output = Array();
    //         $command = Yii::getAlias('@console').'/yii migrate/down --interactive=0';
    //         $command1 = Yii::getAlias('@console').'/yii migrate --interactive=0';
    //         // print_r($command);
    //         // exit();
    //         exec($command, $output);
    //         exec($command1, $output);
    //         // exec(\Yii::getAlias('@console'). '/yii migrate  --interactive=0', $output1, $return2);
    //         echo implode("\n", $output);
    //         // if(!$return) {
    //         //     Yii::$app->session->setFlash('success', Yii::t('backend', $str));
    //         //     return $this->render('tools');
    //         // }
    //     }
    //     catch(\Exception $ex)
    //     {
    //         echo $ex->getMessage();
    //     }
    //     return htmlentities(ob_get_clean(), null, Yii::$app->charset);
    // }
}
