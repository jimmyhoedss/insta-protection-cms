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


class PolicyController extends \yii\web\Controller
{
    public $layout = '@app/views/layouts/mobile';
    // public $layout = false;

    public function actionMyipSpCp01()
    {
        return $this->render('myip-sp-cp01');
    }

    public function actionSgipSpCp01()
    {
       return $this->render('sgip-sp-cp01');
    }

    public function actionIntipSpCp01()
    {
       return $this->render('intip-sp-cp01');
    }





}