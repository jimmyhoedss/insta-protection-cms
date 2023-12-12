<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;
use kartik\grid\DataColumn;
use kartik\grid\GridView;
use common\models\UserProfile;
use common\models\User;
use common\models\UserCase;
use common\models\UserPlanAction;
use common\models\UserPlanActionDocument;
use common\models\UserPlanDetail;
use common\models\UserPlanDetailEdit;
use common\models\DealerCompany;
use common\models\InstapPlanPool;
use common\models\UserPlanDetailEditHistory;
use yii\widgets\ActiveForm;
use common\components\MyCustomActiveRecord;
use \yii\web\YiiAsset;
use common\components\Utility;
use common\widgets\MyUpload\MyUpload;
use yii\bootstrap\Tabs;
use yii\web\JsExpression;



YiiAsset::register($this);

$status = InstapPlanPool::allPlanStatus()[$model->plan_status];
$this->title = $model->policy_number;
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Policy Activations'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;


?>

<div class="instap-plan-pool-update">

<h4 class="sub-title"><?=Yii::t('backend','Policy Details')?></h4>
<?php echo $model->getPolicyDetailLayout(); ?>

<hr>
<h4 class="sub-title"><?=Yii::t('backend','User Details')?></h4>
<?php echo $model->user->userProfile->getUserDetailLayout(); ?>

<hr>
<h4 class="sub-title"><?=Yii::t('backend','Policy Details')?></h4>
<?php
    //only plan that completed registration will have plan details (eg. model brand, imei, etc)
    $userPlanDetail = UserPlanDetail::find()->andWhere(['plan_pool_id'=> $model->id])->one();
    $html = Yii::t('backend',"Not available");
    if ($userPlanDetail != null) {
        $link = Url::to(['user-plan-detail/view', 'plan_pool_id' => $model->id]);
        $html = "<a class='fa fa-pencil' href=" . $link . "> ".Yii::t('backend','Edit Policy Details')."</a>";
        if ($userPlanDetail->hasEdit()) {
            $cta = Yii::t('backend', "There are edits pending for approval.");
            $html = "<a class='' href=" . $link . ">".$cta."</a>";
        }
        $html .= UserPlanDetail::getPlanDetailLayoutByModel($userPlanDetail);     
    }
    echo $html;    
    
?>

<!-- Plan action -->
<hr>

    <h4 class="sub-title"><?=Yii::t('backend','Policy Activities')?></h4>

    <?php echo GridView::widget([
        'dataProvider' => $dataProvider,
        // 'filterModel' => $searchModel,
        'layout' => '{items}{pager}',
        'columns' => [
            ['class' => 'yii\grid\SerialColumn',
            'headerOptions' => ['width' => '20px']
        	],
            [
                'label' => Yii::t('backend',"Name"),
                'format' => 'raw',
                'attribute' => 'full_name',
                'value' => function($model) { 
                    $user = User::find()->andWhere(['id'=>$model->created_by])->one();
                    $link = Url::to(['user/view', 'id' => $model->updated_by]);
                    return $user->userProfile->getAvatarSmallLayout($link);
                },
                'headerOptions' => ['width' => '220px'],
            ],
            [
                'label'=>Yii::t('backend','Action'),
                'format' => 'raw',
                'value' => function($model) {
                    $s = UserPlanAction::allActionStatus()[$model->action_status];
                    return $s;
                }, 
                'headerOptions' => ['width' => '150px'],

            ],
            [
                'attribute'=>'notes_user',
                'format' => 'html',
                'value' => function($model) {
                    $html = (($model->action_status == UserPlanAction::ACTION_APPROVE) ? "<b>Product Price : </b>" : "") . $model->notes_user;
                    
                    if($model->action_status == UserPlanAction::ACTION_REGISTRATION) {
                        $d = UserPlanDetail::find()->andWhere(['plan_pool_id'=>$model->plan_pool_id])->one();
                            if($d) {
                            $html .= $d->getPlanDetailSmallLayout();                                
                        } else {
                            $html .= "<p> No info provided by user!!</p>"; 
                        }
                    }

                    $arr = UserPlanActionDocument::find()->andWhere(['plan_action_id'=>$model->id])->asArray()->all();
                    foreach ($arr as $key => $value) {
                        $base = $value['thumbnail_base_url'];
                        $path = $value['thumbnail_path'];
                        
                        $path = Utility::replacePath($path);
                        $link = Utility::getPreSignedS3Url($path);

                        // $link = $base . "/" . $path ;
                        $html .= "<a href=".$link." target='_blank'><img class='photo x-small' src='". $link."'></a> ";
                    }
                            
                    return $html;
                },                 
                'headerOptions' => ['width' => '*'],
            ],
            [
                'attribute' => 'notes_user',
                'format' => 'raw',                
                'value' => function($model) {
                    return "" . $model->notes;
                },
                'headerOptions' => ['width' => '*'],
            ],
            [
                'label'=> Yii::t('backend','Done At'),
                //'attribute'=>'created_at',
                'format' => 'raw',
                'value' => function($model) {
                    $d = Yii::$app->formatter->asDatetime($model->created_at);
                    return $d;
                },                 
                'headerOptions' => ['width' => '100px'],
            ],         
        ],
    ]);

    //$list = UserPlanAction::allActionStatus();
    $list = UserPlanAction::processPlanActionStatus();
    // print_r($model->errors);
    //if ($model->plan_status == InstapPlanPool::STATUS_PENDING_APPROVAL) {
        $form = ActiveForm::begin(['enableClientValidation'=>false, 'options' => [ 'id' => 'plan-status-form']]); 
        echo $form->errorSummary($model); 
        echo $form->errorSummary($modelAction);

        $ddl = $form->field($modelAction, 'action_status')->dropDownList($list, ['id' => 'plan-status', 'prompt' => ['text'=> '--Select--', 'options'=> ['disabled' => true, 'selected' => true]]])->label("Update Policy Status");
        $ta = $form->field($modelAction, 'notes_user')->textarea(['rows' => 4, 'maxlength'=>256, 'placeholder' => "No more than 256 characters"]);
        $pp = $form->field($modelPlanDetail2, 'sp_device_purchase_price')->textInput(['type' => 'number','step'=>'any','placeholder' => "Price must be in numerical value"]);
        $image = $form->field($planActionForm, 'photo_assessment', [ 'options' => [ 'style' => 'color: red']])->widget(
                MyUpload::className(),[
                    'url' => ['/file-storage/upload'],
                    'maxFileSize' => 5000000, // 5 MiB
                    'uploadPath' => 'media/device',
                    'maxNumberOfFiles' => 2,
                    'acceptFileTypes' => new JsExpression('/(\.|\/)(jpe?g|png)$/i'),
                ])->label("")->hint("<i>Submit in jpeg or png format</i>");
        $image2 = $form->field($planActionForm, 'photo_registration', [ 'options' => [ 'style' => 'color: red']])->widget(
                MyUpload::className(),[
                    'url' => ['/file-storage/upload'],
                    'maxFileSize' => 5000000, // 5 MiB
                    'uploadPath' => 'media/user-plan',
                    'maxNumberOfFiles' => 2,
                    'acceptFileTypes' => new JsExpression('/(\.|\/)(jpe?g|png)$/i'),
                ])->label("")->hint("<i>Submit in jpeg or png format</i>");
        $ta2 = $form->field($modelAction, 'notes')->textarea(['rows' => 4, 'maxlength'=>256, 'placeholder' => "No more than 256 characters"]);
        

        $html = $ddl;
        $html .= '<section class="form-group status-purchase-price" style="display: block;">';
        $html .= $pp;
        $html .= '</section>';
        $html .= '<section class="form-group status-upload-assess-photo" style="display: block;">';
        $html .= $image;
        $html .= '</section>';
        $html .= '<section class="form-group status-upload-reg-photo" style="display: block;">';
        $html .= $image2;
        $html .= '</section>';
        $html .= '<section class="form-group status-note" style="display: block;">';
        $html .= $ta;
        $html .= '</section>';
        $html .= $ta2;

        $but = Html::submitButton(Yii::t('backend', 'Save'), ['class' => 'btn btn-success submit-btn']);
        $html .= '<div class="form-group">' . $but . '</div>';

        echo $html;
        ActiveForm::end();

    //}


?>


</div>

<?php

//actions that requires notes
$arr = array_keys(UserPlanAction::requireNotesActionStatus());
$str =  Utility::enclose_quotes_str($arr);
$planArr = json_encode(UserPlanAction::allActionStatus());
// $str1 =  Utility::enclose_quotes_str($planArr);

$script = <<< JS

const arr = [{$str}];
const planArr = {$planArr};

$(document).ready(function () {
    
    $('#plan-status').on('change', function (e) {
        $("section.form-group").not(".field-plan-status").hide();
        checkStatusField();
    });

    function checkStatusField() {
        $('#plan-status-form').off('beforeSubmit');
        var val = $('#plan-status').val(); 
        var prompt = "Do you really want to ["+ planArr[val] +"]";
        if (arr.indexOf(val) != -1) {
            $("section.status-note").show();
        }else if(val == "approve") {
            $("section.status-purchase-price").show();
        }else if(val == "physical_assessment") {
            $("section.status-upload-assess-photo").show();
        }else if(val == "upload_photo" || val == "upload_photo_admin" ){
            $("section.status-upload-reg-photo").show();
        }

        $('#plan-status-form').on('beforeSubmit', function (e) {
            if (!confirm(prompt)) {
                return false;
            }
            return true;
        });
    }

    $('#plan-status').trigger('change');
});

JS;
$this->registerJs($script);


?>
