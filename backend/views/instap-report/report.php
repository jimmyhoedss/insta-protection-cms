<?php

use yii\helpers\Html;
use yii\grid\GridView;
use kartik\export\ExportMenu;



/* @var $this yii\web\View */
/* @var $model common\models\InstapReport */

$this->title = Yii::t('backend', 'Generate {modelClass}', [
    'modelClass' => 'Report',
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Report Generation History'), 'url' => ['declaration-report']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="instap-report-create">

    <?php echo $this->render('_form', [
        'model' => $model,
    ]) ?>


</div>
