<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use backend\assets\ChartAsset;
use yii\web\View;
use common\assets\FontAwesome;
use backend\assets\BackendAsset;
use common\models\DealerCompany;
use common\models\DealerCompanyDealer;




/* @var $this yii\web\View */
/* @var $model common\models\DealerCompanyDealer */

$this->title = Yii::t('backend', "Company relation");
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Dealer Company Dealers'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// ChartAsset::register($this);
// FontAwesome::register($this);
// $bundle = BackendAsset::register($this);
?>

<div class="dealer-company-dealer-view">


    <p>
        <?= Html::a(Yii::t('backend', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('backend', 'Delete'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('backend', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'dealer_company_upline_id',
            'dealer_company_downline_id',
        ],
    ]) ?>

</div>

