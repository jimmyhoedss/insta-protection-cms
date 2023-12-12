<?php

namespace frontend\controllers;

use Yii;
use common\models\UserPlanDetail;
use common\models\InstapPlanPool;
use common\models\form\RegistrationForm;
use common\models\form\VerifyAccountForm;
use common\models\form\PasswordResetRequestForm;
use common\models\form\PasswordResetForm;
use frontend\views\user\resetPassword;
use yii\web\UnauthorizedHttpException;
use yii\web\BadRequestHttpException;


class UserController extends \yii\web\Controller
{
    public $layout = '@app/views/layouts/default';

    public function actionThankYou()
    {
        return $this->render('thank-you');
    }

    /*
    //LOYNOTE:: Registration is done in app
    public function actionRegister()
    {
    	$model = new RegistrationForm();

       if ($model->load(Yii::$app->request->post()) && $model->register()) {
                 //return $this->goHome();
                Yii::$app->session->setFlash('success', "Data saved!.");                 
                return $this->render("thank-you");
        }

        return $this->render('register', [
            'model' => $model
        ]);
    }
    */

    public function actionVerify($token) {
        $model = new VerifyAccountForm();
        $model->token = $token;

        if ($model->validateToken() && $model->verifyAccount()) {
            return $this->render("result", [
                "title" => "Email Verified",
                "msg" => "Your email (" . $model->email . ") has been verified."
            ]);
                
        } else {
            return $this->render("result", [
                "title" => "Verification Error",
                "msg" => "Your token is invalid or has expired."
            ]);
            //print_r( $model->getErrors() );
            //exit();
            throw new BadRequestHttpException("Bad Token");
        }
    }

    // public function actionRequestPasswordReset()
    // {
    //     $model = new PasswordResetRequestForm();
    //     if ($model->load(Yii::$app->request->post()) && $model->validate()) {
    //         if ($model->sendEmail()) {

    //         } else {
    //             //dun tell user if email doesn't exist for security reasons
    //         }

    //         return $this->render("result", [
    //             "title" => "Request Password Reset",
    //             "msg" => "An email has been sent to <b>" . $model->email . "</b>. <span class='nobr'>Please check your email for further instructions.</span>"
    //         ]);
    //     }

    //     return $this->render('requestPasswordReset', [
    //         'model' => $model,
    //     ]);
    // }

    // public function actionPasswordReset($token)
    // {
    //     $model = new PasswordResetForm();
    //     $model->token = $token;
    //     $msg = "";
    //     if ($model->validateToken()) {
    //         $msg = "Please enter a new password for <b>" . $model->email . "</b>.";
    //     } else {   
    //         return $this->render('invalidToken', ['model' => $model]);
    //     }        

    //     if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {

    //         return $this->render("result", [
    //             "title" => "Password Reset Successful",
    //             "msg" => "The password for <b>" . $model->email . "</b>. has been successfully reset."
    //         ]);
    //     } 

    //     return $this->render('resetPassword', [
    //         'msg' => $msg,
    //         'model' => $model,
    //     ]);
    // }



}