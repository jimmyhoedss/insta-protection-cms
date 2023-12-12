<?php

namespace backend\controllers;

use Yii;
use common\models\User;
use common\models\SysUserToken;
use common\models\DealerUser;
use common\models\SysOAuthAccessToken;
use common\models\form\OtpForm;
use common\models\form\LoginForm;
use yii\helpers\ArrayHelper;

/**
 * Site controller
 */
class SiteController extends \yii\web\Controller
{
    const FORCE_LOGOUT_TARGET_SYSTEM = 1;
    const FORCE_LOGOUT_TARGET_COMPANY = 2;
    const FORCE_LOGOUT_TARGET_INDIVIDUAL = 3;

    public $layout = 'base';

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }
    
    public function actionIndex() {
        return $this->render('index');
    }

    //only admin can login backend with email_admin
    public function actionLogin() {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $session = Yii::$app->session;
        $session->remove('mobile_number_full');
        $form = new OtpForm();
        if ($form->load(Yii::$app->request->post())) {
            $temp = $form->mobile_calling_code . '' . $form->mobile_number;
            $form->mobile_number_full = $temp;
            if ($form->validate() && $user = $form->getAdminUser()) {
                $form->sendSms($user, SysUserToken::TYPE_ONE_TIME_PASSWORD_CMS);
                $session->set('mobile_number_full', $form->mobile_number_full);
                return $this->redirect('otp');
            }
        }
        //print_r($form->errors);        
        return $this->render('login-otp', [
            'model' => $form
        ]);
    }

    public function actionLogin2() {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $session = Yii::$app->session;
        $form = new LoginForm();
        $form->scenario = LoginForm::EMAIL_LOGIN;
        if ($form->load(Yii::$app->request->post())) {
            if ($form->validate() && $user = $form->getAdminUser()) {
                 if ($user = $form->loginBackend2()) {
                    $country_access_permissions = $user->grantedCountryAccessPermissions;

                    if(count($country_access_permissions) > 0){
                        $region_id = $country_access_permissions[0];
                        $session->set('region_id', strtoupper(substr($region_id,-2)));
                    } else {
                        $session->set('region_id', Yii::$app->user->identity->region_id);
                    }

                    $this->goHome();
                }
            }
        }
        //print_r($form->errors);        
        return $this->render('login-email', [
            'model' => $form
        ]);
    }

    public function actionResendOtp(){
        $request = \Yii::$app->getRequest();
        $success = false;
        $errors = array();
        $form = new OtpForm();
        $form->setScenario('resend');

        if ($request->isPost && $form->load($request->post())) {
            if ($form->validate() && $user = $form->getAdminUser()) {
                if($form->sendSms($user, SysUserToken::TYPE_ONE_TIME_PASSWORD_CMS)){
                    $success = true;
                }
            }
        }
        if ($form->hasErrors()) {
            $errors =  $form->getErrors();
        }
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return [
            'success' => (bool)$success,
            'errors' => $errors,
        ];
    }

    public function actionOtp() {
        $session = Yii::$app->session;
        $mf = $session->get('mobile_number_full');
        if ($mf && $mf != "") {
            $form = new LoginForm();  
            $form->scenario = LoginForm::CMS_LOGIN;
            $form->mobile_number_full = $mf;
            if($form->load(Yii::$app->request->post())) {
                if ($user = $form->loginBackend())  {
                    $session->remove('mobile_number_full');

                    $country_access_permissions = $user->grantedCountryAccessPermissions;
                    if(count($country_access_permissions) > 0){
                        $region_id = $country_access_permissions[0];
                        $session->set('region_id', strtoupper(substr($region_id,-2)));
                    } else {
                        $session->set('region_id', Yii::$app->user->identity->region_id);
                        // $session->set('region_id', strtoupper(substr(User::PERMISSION_IP_ACCESS_SG,-2)));
                    }

                    $this->goHome();
                }
            }
            //print_r($form->errors);
            return $this->render('otp', [
                'model' => $form
            ]);
        } else {
            return $this->redirect('login');
        }
    }


    public function actionLogout() {
        Yii::$app->user->logout();
        return $this->redirect('login');
        //return $this->goHome();
    }

    public function actionTest() {
        return $this->render('test');
    }

    public function actionCountry($choice) {
        $session = Yii::$app->session;
        $session->set('region_id', strtoupper(substr($choice,-2)));
        ArrayHelper::remove($_GET, 'choice');
        // ArrayHelper::remove($_GET, 'controller');
        // ArrayHelper::remove($_GET, 'action');
        // $url = ArrayHelper::merge([$controller."/".$action], $_GET);
        // return $this->redirect($url);
        return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
    }



}