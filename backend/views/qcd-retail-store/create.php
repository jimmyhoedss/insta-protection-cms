<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\QcdRetailStore */

$this->title = Yii::t('backend', 'Create Retail Store:');
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Retail Stores'), 'url' => ['index']];
$this->params['breadcrumbs'][] = Yii::t('backend', 'Create Retail Store');
?>
<div class="qcd-retail-store-create">

    <?= $this->render('_form', [
        'model' => $model,
        'retailStoreForm' => $retailStoreForm
    ]) ?>

</div>
