<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\DealerUser */

$this->title = Yii::t('backend', 'Add Staff');
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Staffs'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="dealer-user-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
