<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\DealerCompany */

$this->title = Yii::t('backend', 'Create Company');
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Companies'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="dealer-create">


    <?= $this->render('_form', [
        'model' => $model,
        'modelCompanyPlan' => $modelCompanyPlan,
        'modelCompanyRelation' => $modelCompanyRelation
    ]) ?>

</div>
