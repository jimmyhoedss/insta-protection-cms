<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\BadRequestHttpException;
use yii\filters\VerbFilter;

class ControllerBase extends Controller
{
    public $layout = "common";

    public function behaviors() {
        $behaviors = parent::behaviors();

        return $behaviors + [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];

        return $behaviors;
    }

    public function init() {

    }

    public function actions() {
        return [
            'error' => ['class' => 'yii\web\ErrorAction'],
        ];
    }


    public function beforeAction($action) {

        return parent::beforeAction($action);;
    }

}


