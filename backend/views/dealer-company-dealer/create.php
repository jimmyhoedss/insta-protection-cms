<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\DealerCompanyDealer */

$this->title = Yii::t('backend', 'Create Company Relationship');
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Company Relationships'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="dealer-company-dealer-create">


    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
