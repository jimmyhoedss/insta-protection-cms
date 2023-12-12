<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\QcdRepairCentre */

$this->title = Yii::t('backend', 'Update Repair Centre:');
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Repair Centres'), 'url' => ['index']];
$this->params['breadcrumbs'][] = Yii::t('backend', 'Update Repair Centre');
?>
<div class="qcd-repair-centre-create">

    <?= $this->render('_form', [
        'model' => $model,
        'repairCentreForm' => $repairCentreForm
    ]) ?>

</div>
