<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use common\models\UserPlanDetailEditHistory;
use common\models\UserPlanDetail;
use common\models\User;
use yii\helpers\Url;


/* @var $this yii\web\View */
/* @var $model common\models\UserPlanDetail */

$this->title = "Policy Details: ". $model->planPool->policy_number;
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Policy Activations'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', $model->planPool->policy_number), 'url' => ['instap-plan-pool/update','id'=> $model->plan_pool_id]];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="user-plan-detail-view">

    <?php 
        $html = "<h4 class='sub-title'>Policy Details </h4>";
        $html .= $model->planPool->getPolicyDetailLayout();
        $html .= "<hr>";
        $html .= "<h4 class='sub-title'>Plan Details (Old)</h4>";
        $html .= UserPlanDetail::getPlanDetailLayoutByModel($model);

        if (Yii::$app->user->can(User::PERMISSION_IP_EDIT)) {
             $html .= Html::a('Edit', ['user-plan-detail/edit', 'plan_pool_id' => $model->plan_pool_id], ['class' => 'btn btn-primary']);
             $html .="&nbsp;";
        }
        
        if ($model->planDetailEdit != null) {
            $html .= "<hr>";
            $html .= "<h4 class='sub-title'>Plan Details (New)</h4>";
            $html .= UserPlanDetail::getPlanDetailLayoutByModel($model->planDetailEdit);
            $html .= "<h4 class='sub-title'>Notes:</h4>";
            $html .= "<div class='jumbotron'><p>".$model->planDetailEdit->notes."</p></div>";
            if (Yii::$app->user->can(User::PERMISSION_IP_APPROVE)) {
                // PERMISSION_APPROVE
                $html .= Html::a('Approve', ['edit-approve', 'plan_pool_id' => $model->plan_pool_id], 
                    ['class' => 'btn btn-success']);
                $html .= " ";
                $html .= Html::a('Reject', ['edit-reject', 'plan_pool_id' => $model->plan_pool_id], 
                    ['class' => 'btn btn-danger']);
            }
        }
        
        echo $html;

    ?>

    <hr>
    <h4 class="sub-title">Plan Details Edit History</h4>
    <?php
        $models = $model->getEditHistory();
        if(empty($models)) {
            $html = "Not history record";
        } else {
            $html = UserPlanDetailEditHistory::getPlanDetailEditHistoryLayout($models);
        }
        echo $html;
    ?>
 
</div>

