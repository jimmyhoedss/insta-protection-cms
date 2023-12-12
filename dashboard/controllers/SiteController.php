<?php

namespace dashboard\controllers;

use Yii;
use common\models\User;
use common\models\SysUserToken;
use common\models\form\OtpForm;
use common\models\form\LoginForm;
use yii\helpers\ArrayHelper;

/**
 * Site controller
 */
class SiteController extends \yii\web\Controller
{
    public $layout = 'base';

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => null,
                'offset' => 1
            ],
            // 'set-locale' => [
            //     'class' => 'common\actions\SetLocaleAction',
            //     'locales' => Yii::$app->params['availableLocales'],
            //     'localeCookieName'=>'_locale',
            // ]
        ];
    }

    public function actionIndex() {
        return $this->render('index');
    }

    public function actionLogin() {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $session = Yii::$app->session;
        $session->remove('mobile_number_full');
        $session->remove('flag_method');
        $form = new OtpForm();
        $form->scenario = OtpForm::CAPTCHA;
        
        if ($form->load(Yii::$app->request->post())) {
            $temp = $form->mobile_calling_code . '' . $form->mobile_number;
            $form->mobile_number_full = $temp;
            
            if ($form->validate() && $user = $form->getUser()) {
                // check what type to send otp
                // print_r($form->flag_method);
                // exit();
                if(!is_null($form->sendSms($user, SysUserToken::TYPE_ONE_TIME_PASSWORD_CMS))){
                    $session->set('mobile_number_full', $form->mobile_number_full);
                    $session->set('flag_method', $form->flag_method);
                    return $this->redirect('otp');
                }
            }            
            $session->remove('mobile_number_full');
            $session->remove('flag_method');
        }
        if ($form->hasErrors()) {
            $errors =  $form->getErrors();
            User::sendTelegramBotMessage(json_encode($form->attributes));
            User::sendTelegramBotMessage(json_encode($errors));
        }
        //print_r($form->errors);        
        return $this->render('login', [
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
        $flag_method = $session->get('flag_method');
        if ($mf && $mf != "") {
            $form = new LoginForm();  
            $form->scenario = LoginForm::CMS_LOGIN;
            $form->mobile_number_full = $mf;
            if($form->load(Yii::$app->request->post())) {                
                if ($user = $form->loginDashboard())  {
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
                'model' => $form,
                'flag_method' => $flag_method,
            ]);
        } else {
            return $this->redirect('login');
        }
    }


    public function actionLogout() {
        Yii::$app->user->logout();
        return $this->goHome();
    }

    public function actionTest() {
        return $this->render('test');
    }

}