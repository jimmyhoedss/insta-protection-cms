<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\InstapPromotion */

$this->title = Yii::t('backend', 'Create Promotional Banner');
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Promotional Banner'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="instap-banner-create">


    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
