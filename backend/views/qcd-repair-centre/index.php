<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\models\QcdDeviceMakerRepairCentre;
/* @var $this yii\web\View */
/* @var $searchModel common\models\search\QcdRepairCentreSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('backend', 'Repair Centres');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="qcd-repair-centre-index">

    <p>
        <?= Html::a(Yii::t('backend', 'Create Repair Centre'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn',
                'headerOptions' => ['width' => '20px'],
            ],
            [          
                'label'=>Yii::t('backend', 'Country'),
                'format' => 'raw',
                'filter' => false,
                'attribute' => 'country_code',                
                'headerOptions' => ['width' => '20'],
            ],
            [          
                'label'=>Yii::t('backend', 'Name'),
                'format' => 'raw',
                'attribute' => 'repair_centre',                
                'headerOptions' => ['width' => '250'],
            ],
            [   
                'label' => Yii::t('backend', 'Address'),
                'attribute' =>  'address',
                'value' => function($model) {
                    $html = "" . $model->address;
                    return $html;
                },                 
                'headerOptions' => ['width' => '*'],  
            ],
            // 'opening_hours',
            // 'email',
            // 'telephone',
            [          
                'label'=>Yii::t('backend', 'Brand'),
                'format' => 'raw',
                'value' => function($model) { 
                    $repair_centres = QcdDeviceMakerRepairCentre::find()->where(['repair_centre_id' => $model->id])->all();
                    $html = $model->getBrandLayout($repair_centres);
                    return $html;
                },                 
                'headerOptions' => ['width' => '250'],
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template'=>'{update} {delete}',
                'headerOptions' => ['width' => '40px'],
            ],
        ],
    ]); ?>


</div>
