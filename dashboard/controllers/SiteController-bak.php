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
        $form = new OtpForm();
        
        if ($form->load(Yii::$app->request->post())) {
            $temp = $form->mobile_calling_code . '' . $form->mobile_number;
            $form->mobile_number_full = $temp;
            
            if ($form->validate() && $user = $form->getUser()) {
                $form->sendSms($user, SysUserToken::TYPE_ONE_TIME_PASSWORD_CMS);
                $session->set('mobile_number_full', $form->mobile_number_full);
                return $this->redirect('otp');
            }
            
        }
        //print_r($form->errors);        
        return $this->render('login', [
            'model' => $form
        ]);
    }

    public function actionOtp() {
        $session = Yii::$app->session;
        $mf = $session->get('mobile_number_full');
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
                'model' => $form
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