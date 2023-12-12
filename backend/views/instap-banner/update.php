<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\InstapPromotion */

$this->title = Yii::t('backend', 'Update Promotional Banner: {name}', [
    'name' => $model->title,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Promotional Banner'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('backend', 'Update');
?>
<div class="instap-banner-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
