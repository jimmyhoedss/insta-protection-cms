<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\DealerUserHistory */

$this->title = Yii::t('frontend', 'Create Dealer User History');
$this->params['breadcrumbs'][] = ['label' => Yii::t('frontend', 'Dealer User Histories'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="dealer-user-history-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
