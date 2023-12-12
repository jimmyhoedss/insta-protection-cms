<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\User */

$this->title = Yii::t('backend', 'Add InstaProtection Staff');
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'InstaProtection Staffs'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-create">

    <?= $this->render('_form', [
        'modelIpStaff' => $modelIpStaff,
    ]) ?>

</div>
