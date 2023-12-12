<?php
namespace console\controllers;

use Yii;

class ReportController extends \yii\console\Controller
{
    public function actionIndex() {
        echo "\n\nReport generation running\n\n";
        // setting env variables
        date_default_timezone_set("Asia/Singapore");
        $model = new \common\models\InstapReport();
        foreach (\common\models\SysRegion::getAllRegions() as $region_id) {
            // SysRegion::MALAYSIA / SysRegion::SINGAPORE / SysRegion::THAILAND
            $model->region_id = $region_id; 
            $model->date_start = strtotime("1st april 2021 0001");
            $model->date_end = strtotime("15th april 2021 2359");
            $model->generateAmTransactionReport();
            $model->generateRetailerTransactionReport();
            $model->generateSohReport();
            $model->generateClaimSubmissionReport();
        }
    }
}
