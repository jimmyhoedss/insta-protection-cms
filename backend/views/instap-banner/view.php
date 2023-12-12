<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\InstapPromotion */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Promotional Banner'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="instap-banner-view">


    <p>
        <?= Html::a(Yii::t('backend', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            // 'id',
            'title',
            'region_id',
            'description:ntext',
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
            // 'thumbnail_path',
            'status',
            // 'created_at',
            // 'created_by',
            // 'updated_at',
            // 'updated_by',
        ],
    ]) ?>

</div>
