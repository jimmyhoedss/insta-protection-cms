<?php

namespace backend\controllers;

use Yii;
use common\models\User;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use kartik\mpdf\Pdf;
use common\components\Utility;

/**
 * ReportController implements the CRUD actions for SysFcmGroup model.
 */
class ReportController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public $filename = "";

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                       //'actions' => ['index'],
                       'allow' => true,
                       // 'roles' => [User::ROLE_ADMINISTRATOR, User::ROLE_IP_ADMINISTRATOR, User::ROLE_IP_MANAGER, User::ROLE_IP_ADMIN_ASSISTANT],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all SysFcmGroup models.
     * @return mixed
     */
    public function actionIncident($id)
    {
        /*Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        $pdf = new Pdf([
            'mode' => Pdf::MODE_UTF8, // leaner size using standard fonts
            'destination' => Pdf::DEST_BROWSER,
            'content' => $this->renderPartial('incident', ['id'=> 12]),
            'options' => [
            'showImageErrors' => true,
                // any mpdf options you wish to set

            ],
            'methods' => [
                'SetTitle' => 'Incident Report',
                // 'SetSubject' => 'Activations by care plans',
                // 'SetHeader' => ['•  No. of Activations by care plans on a daily basis by respective partners<br>Generated On: ' . date("r")],
                // 'SetFooter' => ['|Page {PAGENO}|'],
                'SetAuthor' => 'Instaprotection',
                'SetCreator' => 'Instaprotection',
                'SetKeywords' => 'instaprotection, instaprotect, protection, ip, care plan',
            ]
        ]);
        return $pdf->render();*/

        return $this->render('incident', [
            'id' => $id,
        ]);
    }

}
