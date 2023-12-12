<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\InstapPlan */

$this->title = Yii::t('backend', 'Create Plan');
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Plan Offerings'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="instap-plan-create">


    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
