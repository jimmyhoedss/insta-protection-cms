<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\commands\SendSmsCommand;
use common\commands\SendEmailCommand;
/**
 * UserLocationController implements the CRUD actions for UserLocation model.
 */
class TestController extends Controller
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
    public function actionTestSms()
    {   
        // Yii::$app->commandBus->handle(new SendSmsCommand([
        //     'mobileNumber' => '6597479576',
        //     'message' => 'hey dog',
        // ]));
    }
    public function actionTestEmail()
    {   
        // Yii::$app->commandBus->handle(new SendEmailCommand([        
        //     'subject' => 'loytest email',
        //     'view' => '@common/mail/loytest',
        //     'to' => 'loytheman@gmail.com',
        //     'params' => []
        // ]));
        /*
        Yii::$app->mailer->compose()
        ->setFrom('admin@instaprotection.site')
        ->setTo('loytheman@gmail.com')
        ->setSubject('loytest')
        ->setTextBody('Plain text content')
        ->setHtmlBody('<b>HTML content</b>')
        ->send();
        */
    }

}
