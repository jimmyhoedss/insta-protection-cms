<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\InstapReport */

$this->title = Yii::t('backend', 'Update {modelClass}: ', [
    'modelClass' => 'Instap Report',
]) . ' ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Instap Reports'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('backend', 'Update');
?>
<div class="instap-report-update">

    <?php echo $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
