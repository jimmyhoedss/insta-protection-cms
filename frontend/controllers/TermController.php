<?php

namespace frontend\controllers;

use Yii;
use common\models\form\RegistrationForm;
use common\models\form\VerifyAccountForm;
use common\models\form\PasswordResetRequestForm;
use common\models\form\PasswordResetForm;
use frontend\views\user\resetPassword;
use yii\web\UnauthorizedHttpException;
use yii\web\BadRequestHttpException;


class TermController extends \yii\web\Controller
{
    // public $layout = '@app/views/layouts/mobile';
    public $layout = '@app/views/layouts/default';
    
    // public $layout = false;

    public function actionTerm($region_id = "")
    {   
        // TODO remove this! put it in SiteController instead
        if($region_id == "SG") {
            return $this->render('sgip-sp-cp01');
        } else if($region_id == "MY") {
            return $this->render('myip-sp-cp01');
        } else {
            return $this->render('intip-sp-cp01');
        }
    }

    // public function actionMyipSpCp01()
    // {
    //     return $this->render('myip-sp-cp01');
    //     return $this->render('myip-sp-cp01');
    // }

    // public function actionIntipSpCp01()
    // {
    //    return $this->render('intip-sp-cp01');
    // }




}