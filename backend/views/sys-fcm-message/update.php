<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\SysFcmMessage */

$this->title = 'Update Sys Fcm Message: ' . ' ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Sys Fcm Messages', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="sys-fcm-message-update">

    <?php echo $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
