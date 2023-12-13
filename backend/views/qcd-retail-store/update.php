<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\QcdRetailStore */

$this->title = Yii::t('backend', 'Update Retail Store:');
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Retail Stores'), 'url' => ['index']];
$this->params['breadcrumbs'][] = Yii::t('backend', 'Update Retail Store');
?>
<div class="qcd-retail-store-update">

    <?= $this->render('_form', [
        'model' => $model,
        'brands' =>$brands,
        'retailStoreForm' => $retailStoreForm
    ]) ?>

</div>
