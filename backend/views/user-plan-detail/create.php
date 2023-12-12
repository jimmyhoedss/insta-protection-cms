<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\UserPlanDetail */

$this->title = Yii::t('backend', 'Create User Plan Detail');
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'User Plan Details'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-plan-detail-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
