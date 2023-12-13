<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\models\QcdDeviceMakerRetailStore;
use common\models\QcdInstapPlanRetailStore;
/* @var $this yii\web\View */
/* @var $searchModel common\models\search\QcdRetailStoreSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('backend', 'Retail Store');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="qcd-repair-centre-index">

    <p>
        <?= Html::a(Yii::t('backend', 'Create Retail Store'), ['create'], ['class' => 'btn btn-success']) ?>
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
                'attribute' => 'retail_store',                
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
                    $retail_stores = QcdDeviceMakerRetailStore::find()->where(['retail_store_id' => $model->id])->all();
                    $html = $model->getBrandLayout($retail_stores);
                    return $html;
                },                 
                'headerOptions' => ['width' => '250'],
            ],
            [          
                'label'=>Yii::t('backend', 'Plan'),
                'format' => 'raw',
                'value' => function($model) { 
                    $retail_stores = QcdInstapPlanRetailStore::find()->where(['retail_store_id' => $model->id])->all();
                    $html = $model->getPlanLayout($retail_stores);
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
