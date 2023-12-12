<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\SysFcmTokenHistory */

$this->title = 'Create Sys Fcm Token History';
$this->params['breadcrumbs'][] = ['label' => 'Sys Fcm Token Histories', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sys-fcm-token-history-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
