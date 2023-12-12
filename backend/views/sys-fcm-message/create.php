<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\SysFcmMessage */

$this->title = 'Create Sys Fcm Message';
$this->params['breadcrumbs'][] = ['label' => 'Sys Fcm Messages', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sys-fcm-message-create">

    <?php echo $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
