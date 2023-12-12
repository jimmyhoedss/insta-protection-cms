<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\UserPlanDetail */

$this->title = Yii::t('backend', 'Edit Policy Details: {name}', [
    'name' => $model->planPool->policy_number,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Policy Activations'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', $model->planPool->policy_number), 'url' => ['instap-plan-pool/update','id'=> $model->plan_pool_id]];
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Policy Details: '. $model->planPool->policy_number), 'url' => ['view','plan_pool_id'=> $model->plan_pool_id]];
$this->params['breadcrumbs'][] = Yii::t('backend', 'Edit');
?>
<div class="user-plan-detail-edit">


    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
