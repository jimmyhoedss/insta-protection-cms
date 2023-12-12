<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;
use kartik\grid\DataColumn;
use kartik\grid\GridView;
use common\models\UserPlanAction;
use common\models\UserCaseAction;
use common\models\UserCaseActionDocument;
use common\models\UserProfile;
use common\models\User;
use common\models\UserCase;
use common\models\UserPlanActionDocument;
use common\models\UserPlanDetail;
use common\models\UserPlanDetailEdit;
use common\models\DealerCompany;
use common\models\InstapPlanPool;
use yii\widgets\ActiveForm;
use common\components\MyCustomActiveRecord;
use common\components\Utility;

$this->title = $model->policy_number;
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

$userDetail = UserPlanDetail::find()->andWhere(['plan_pool_id'=> $model->id])->one();
?>

<p>
    <?= $model->plan_status == InstapPlanPool::STATUS_ACTIVE ? Html::a(Yii::t('backend', 'Submit Claim'), ['claim', 'id'=>$model->id], ['class' => 'btn btn-success']) : "" ?>
    <?= $model->plan_status == InstapPlanPool::STATUS_PENDING_CLAIM && $model->userCase->current_case_status == UserCase::CASE_STATUS_CLAIM_REQUIRE_CLARIFICATION ? Html::a(Yii::t('backend', 'Submit Clarification'), ['clarification', 'id'=>$model->id], ['class' => 'btn btn-success']) : "" ?>
</p>

<h4><b><u>Plan Details</u></b></h4>
<?php echo $model->getPolicyDetailLayout(); ?>

<!-- plan details --> 
<?php
    if($userDetail) {    
?>
<hr> <!-- underline -->
<div class="">
    <h4><b><u>Plan Details</u></b></h4>
  <table class="table">
    <thead>
      <tr>
        <th>Brand</th>
        <th>Model number</th>
        <th>Model name</th>
        <th>Serial no</th>
        <th>Imei no</th>
        <th>Device colour</th>
        <th>Device capacity</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><?=$userDetail->sp_brand?></td>
        <td><?=$userDetail->sp_model_number?></td>
        <td><?=$userDetail->sp_model_name?></td>
        <td><?=$userDetail->sp_serial?></td>
        <td><?=$userDetail->sp_imei?></td>
        <td><?=$userDetail->sp_color?></td>
        <td><?=$userDetail->sp_device_capacity?></td>
      </tr>
    </tbody>
  </table>
</div>
<?php 
    }
?>

<!-- Plan action -->
<?php if($model->plan_status == InstapPlanPool::STATUS_PENDING_CLAIM && ($model->userCase->current_case_status == UserCase::CASE_STATUS_CLAIM_REQUIRE_CLARIFICATION || $model->userCase->current_case_status == UserCase::CASE_STATUS_CLAIM_CLOSED || $model->userCase->current_case_status == UserCase::CASE_STATUS_CLAIM_CANCELLED || $model->userCase->current_case_status == UserCase::CASE_STATUS_CLAIM_REJECTED)): ?>
    <?php if(isset($notes) && !empty($notes)): ?>
        <hr>
        <h4 class="sub-title">Message from InstaProtection</h4>
        <div class="jumbotron">
            <p style="font-size: 15px; padding-left: 15px;"><?= $notes ?></p>
        </div>
    <?php endif; ?>
<?php endif; ?>
<hr>
<div class="instap-plan-pool-view">
<div class="">

    <h4><b><u>Plan Activities</u></b></h4>

    <?php echo GridView::widget([
        'dataProvider' => $dataProvider,
        // 'filterModel' => $searchModel,
        'layout' => '{items}{pager}',
        'columns' => [
            ['class' => 'yii\grid\SerialColumn',
            'headerOptions' => ['width' => '20px']
            ],
            [
                'label' => "Created At",
                'format' => 'raw',
                'attribute' => 'created_at',
                'value' => function($model) { 
                    $u = User::find()->andWhere(['id'=>$model->created_by])->one();
                    $userProfile = $u->userProfile;
                    // $user_auth = Yii::$app->authManager;
                    $d = Yii::$app->formatter->asDatetime($model->created_at);
                    $roles = Yii::$app->authManager->getRolesByUser($model->created_by);
                    $str = "";
                    $i=0;
                    foreach ($roles as $key => $value) {
                        $i++;
                        $str .= ucwords(str_replace('_', ' ', $key)) . "" . (count($roles) <= 1 || $i==(count($roles)) ? "" : ", ");
                    }
                    $link = Url::to(['user/view', 'id' => $model->created_by]);
                    $html =  $userProfile->avatarPic . $userProfile->fullName . " <i>[".($str)."]</i>";
                    $html.= "<br>".($d);

                    return $html;
                
                    
                },
                'headerOptions' => ['width' => '100px'],
            ],
            [
                'label'=>'Action status',
                'format' => 'raw',
                'attribute' => 'action_status',
                'value' => function($model) {
                    $d = ucwords(str_replace('_', ' ', $model->action_status));
                    return $d;
                }, 
                'headerOptions' => ['width' => '150px'],

            ],
            /*[
                'attribute' => 'notes',
                'format' => 'raw',
                'headerOptions' => ['width' => '300px'],
                'value' => function($model) {
                    $html = $model->notes;
                    $link = Url::to(['instap-banner/update', 'id' => $model->id]);
                    $a = $model->action_status;

                    switch($a){

                        case UserPlanAction::ACTION_REGISTRATION:
                            $d = UserPlanDetail::find()->andWhere(['plan_pool_id'=>$model->plan_pool_id])->one();
                            if($d){
                                $html .= "<div style = 'display: inline-flex;'>";
                                $html .= "<div style= 'margin-right: 15px;''float: left;'>";
                                $html .= "<b>Brand:</b> ".$d->sp_brand."<br>";
                                $html .= "<b>Model:</b> ".$d->sp_model_number."<br>";
                                $html .= "<b>S/N:</b> ".$d->sp_serial."<br>";
                                $html .= "</div>";
                                $html .= "<div style= 'padding: 0rem;''float: left;'>";
                                $html .= "<b>IMEI:</b> ". $d->sp_imei."<br>";
                                $html .= "<b>Colour:</b> ".$d->sp_color;
                                $html .= "</div>";
                                $html .="</div>";
                            }
                            else{ 
                                $html .= "<p> No info provided by user!!</p>"; 
                            }  
                        break;

                        case UserPlanAction::ACTION_UPLOAD_PHOTO:

                        case UserPlanAction::ACTION_PHYSICAL_ASSESSMENT:

                        case UserPlanAction::ACTION_REGISTRATION_RESUBMIT:
                            $html = "";
                            $arr = UserPlanActionDocument::find()->andWhere(['plan_action_id'=>$model->id])->all();
                            foreach ($arr as $key => $value){
                                $path = $value['thumbnail_path'];
                                $link = Utility::getPreSignedS3Url($path);                                $html .= "<a href=".$link." target='_blank'><img class='photo x-small' src='". $link."'></a> ";
                            }
                            return $html;
                        break;

                        case UserCaseAction::ACTION_CLAIM_SUBMIT:
                            $caseAction = UserCaseAction::find()->andWhere(['id'=>$model->id])->one();
                            $case = UserCase::find()->andWhere(['id'=>$caseAction->case_id])->one();
                            return "Description: " . $case->description;
                        break;

                        case UserCaseAction::ACTION_CLAIM_UPLOAD_PHOTO:
                            $arr = UserCaseActionDocument::find()->andWhere(['case_action_id'=>$model->id])->all();
                            foreach ($arr as $key => $value){
                                $path = $value['thumbnail_path'];
                                $link = Utility::getPreSignedS3Url($path);
                                $html .= "<a href=".$link." target='_blank'><img class='photo x-small' src='". $link."'></a> ";
                            }
                            return $html;
                        break;

                        default:
                            return "";
                        break;
                    }

                   
                     return $html;
                }
            ],
            [
                'class' => DataColumn::className(),
                'attribute'=>'notes_user',
                'format' => 'html',
                'value' => function($model) {
                    return $model->notes_user;
                },                 
                'headerOptions' => ['width' => '250px'],
                'contentOptions' => [
                    'style'=>'max-width:250px; min-height:100px; overflow: auto; word-wrap: break-word;'
                ],
            ],*/
        ],
    ]); ?>

</div>
