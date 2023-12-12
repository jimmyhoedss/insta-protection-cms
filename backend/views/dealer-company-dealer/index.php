<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use common\models\DealerCompany;
use common\models\User;
/* @var $this yii\web\View */
/* @var $searchModel common\models\search\DealerCompanyDealerSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('backend', 'Company Relationships');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="dealer-company-dealer-index">

    <p>
        <?= Html::a(Yii::t('backend', 'Create Company Relationship'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'label' => Yii::t('backend', 'Upline'),
                'attribute'=>'company_name',
                'format' => 'raw',
                'value' => function($model) {
                    $d = DealerCompany::find()->andWhere(['id' =>$model->dealer_company_upline_id])->one();
                    $link = Url::to(["dealer-company/view", 'id'=>$model->dealer_company_upline_id]);
                    $html = $d->getContactSmallLayout($link);
                    return $html;
                },
                'headerOptions' => ['width' => '*'],
            ],
            [
                'label' => Yii::t('backend', 'Downline'),
                'attribute'=>'company_name2',
                'format' => 'raw',
                'value' => function($model) {
                    $d = DealerCompany::find()->andWhere(['id' =>$model->dealer_company_downline_id])->one();
                    $link = Url::to(["dealer-company/view", 'id'=>$model->dealer_company_downline_id]);
                    $html = $d->getContactSmallLayout($link);
                    return $html;
                },
                'headerOptions' => ['width' => '*'],
            ],
            [
                'label' => Yii::t('backend', 'Organisation'),
                'format' => 'raw',
                'value' => function($model) {
                    $html = "";
                    $role_admin = Yii::$app->authManager->checkAccess(Yii::$app->user->id, User::ROLE_ADMINISTRATOR);
                    if($role_admin){
                        $link1 = Url::to(["dealer-company/chart-admin", 'id'=>$model->dealer_company_upline_id]);
                        $html .= " <a href='".$link1."' title='admin view'><i class='fa fa-sitemap' style='color:red; margin-right:5px;' ></i></a>";

                    } 
                    $link = Url::to(["dealer-company/chart", 'id'=>$model->dealer_company_upline_id]);
                    $html .= " <a href='".$link."'> <i class='fa fa-sitemap' ></i></a>";
                    return $html;
                },
                'headerOptions' => ['width' => '80'],
            ],
            [   
                'class' => 'yii\grid\ActionColumn',
                'template' => '{delete}',
                'headerOptions' => ['width' => '40'],
            ],
        ],
    ]); ?>


</div>
