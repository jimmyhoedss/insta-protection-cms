<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\DealerCompanyDealer */

$this->title = Yii::t('backend', 'Update Company Relationships: {name}', [
    'name' => $model->id,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Dealer Company Dealers'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('backend', 'Update');
?>
<div class="dealer-company-dealer-update">


    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
