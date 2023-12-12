<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\SysFcmGroup */

$this->title = 'Create Message Group';
$this->params['breadcrumbs'][] = ['label' => 'Message Groups', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sys-fcm-group-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
