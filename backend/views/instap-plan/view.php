<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\InstapPlan */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Plan Offerings'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="instap-plan-view">


    <p>
        <?= Html::a(Yii::t('backend', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            // 'id',
            'sku',
            'region_id',
            'name',
            'description:ntext',
            [
                'attribute' => 'coverage_period',
                'format' => 'raw',
                'headerOptions' => ['width' => '120px'],
                'value' => function($model) {
                    return $model->coverage_period. " months";
                }
            ],
            [
                'attribute' => 'ew_coverage_period',
                'format' => 'raw',
                'headerOptions' => ['width' => '120px'],
                'value' => function($model) {
                    return $model->ew_coverage_period. " months";
                }
            ],
            'retail_price',
            'premium_price',
            'dealer_price',
            'status',  
            [
                'attribute' => 'pdf',
                'format' => 'raw',
                'headerOptions' => ['width' => '120px'],
                'value' => function($model) {
                    $html = "";
                    $html .= "<div>";
                    $html .= Html::a('<i class="fa fa-file"></i>', $model->policyPdf, ['class' => 'btn btn-primary', 'target'=>"_blank"]);
                    $html .= "</div>";
                    return $html;
                }
            ],
            [
                'attribute' => 'thumbnail',
                'format' => 'raw',
                'headerOptions' => ['width' => '120px'],
                'value' => function($model) {
                    $link = Url::to(['instap-banner/update', 'id' => $model->id]);
                    $html = "<a>";
                    $html .= $model->getImage("medium"). " ";
                    $html .= "</a>";
                    return $html;
                }
            ],
            // 'created_at',
            // 'created_by',d
            // 'updated_at',
            // 'updated_by',
        ],
    ]) ?>

<!--     <?= Html::a(Yii::t('backend', 'Order'), ['order', 'id' => $model->id], [
            'class' => 'btn btn-success',
            'data' => [
                'confirm' => Yii::t('backend', 'Are you sure you want to order this item?'),
                'method' => 'post',
            ],
        ]) ?>  -->

</div>
