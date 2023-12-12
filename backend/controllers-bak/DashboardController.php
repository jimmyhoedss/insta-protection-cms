<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use common\models\User;
use common\models\DealerOrder;
use yii\web\View;
use common\models\search\TimelineEventSearch;

class DashboardController extends Controller
{
    public $layout = 'common';

    public function behaviors()
    {
        return [
           
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                       //'actions' => ['index'],
                       'allow' => true,
                       //'roles' => [User::ROLE_ADMINISTRATOR],
                       'roles' => ['@'],
                    ],
                ],
            ],
            /**/
        ];
    }
    public function actionIndex()
    {
        $searchModel = new TimelineEventSearch();
        $salesOfYear = DealerOrder::totalSalesOfYear();
        $salesOfYear2 = DealerOrder::totalSalesOfYear2();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $this->getView()->registerJsVar("_data", $salesOfYear, View::POS_BEGIN);
        $this->getView()->registerJsVar("_data2", $salesOfYear2, View::POS_BEGIN);
        $dataProvider->sort = [
            'defaultOrder' => ['created_at' => SORT_DESC]
        ];

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'salesOfYear' => $salesOfYear,
            'salesOfYear2' => $salesOfYear2
        ]);
    }

    public function actionStatistics(){
        return $this->render('statistics');
    }
}
