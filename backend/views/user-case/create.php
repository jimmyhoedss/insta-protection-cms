<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\UserCase */

$this->title = Yii::t('backend', 'Create {modelClass}', [
    'modelClass' => 'User Case',
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'User Cases'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-case-create">

    <?php echo $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
