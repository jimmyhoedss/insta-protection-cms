<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\DealerCompany */

$this->title = Yii::t('backend', 'Update Company: {name}', [
    'name' => $model->business_name,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Companies'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->business_name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('backend', 'Update');
?>
<div class="dealer-update">


    <?= $this->render('_form', [
        'model' => $model,
        'modelCompanyPlan' => $modelCompanyPlan,
        'modelCompanyRelation' => $modelCompanyRelation
        
    ]) ?>

</div>
