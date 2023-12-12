<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\InstapPlanPool */

$this->title = Yii::t('backend', 'Update Policy: {name}', [
    'name' => $model->id,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Policy Activations'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('backend', 'Update');
?>
<div class="instap-plan-pool-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

    <h3>
        Past actions:
    </h3>

    <?php echo GridView::widget([
        'dataProvider' => $dataProvider,
        // 'filterModel' => $searchModel,
        'columns' => [
            [   
                'class' => 'yii\grid\SerialColumn',
                'headerOptions' => ['width' => '20px'],
            ],

            // 'id',
            // 'plan_pool_id',
            // 'description:ntext',
            [
                'attribute'=>'description',
                'format' => 'raw',
                'value' => function($model) {
                    $html = "";
                    switch ($model->action_status) {
                        case UserPlanAction::ACTION_REGISTRATION:
                            $html .= UserPlanAction::ACTION_REGISTRATION;
                            break;
                        
                        case UserPlanAction::ACTION_PHYSICAL_ASSESSMENT:
                            $html .= UserPlanAction::ACTION_PHYSICAL_ASSESSMENT;
                            break;
                        
                        case UserPlanAction::ACTION_REQUIRE_CLARIFICATION:
                            $html .= UserPlanAction::ACTION_REQUIRE_CLARIFICATION;
                            break;
                        
                        case UserPlanAction::ACTION_REGISTRATION_RESUBMIT:
                            $html .= UserPlanAction::ACTION_REGISTRATION_RESUBMIT;
                            break;
                        
                        case UserPlanAction::ACTION_APPROVE:
                            $html .= UserPlanAction::ACTION_APPROVE;
                            break;
                        
                        case UserPlanAction::ACTION_REJECT:
                            $html .= UserPlanAction::ACTION_REJECT;
                            break;
                        
                        default:
                            # code...
                            break;
                    }
                    $details = UserPlanDetail::find()->andWhere(['plan_pool_id'=> $model->plan_pool_id])->one();
                    $html .= "";
                    return $html;
                },                 
                // 'headerOptions' => ['width' => '200px'],
            ],
            'notes_user',
            'action_status',
            // 'status',
            // 'created_at',
            // 'created_by',
            // 'updated_at',
            // 'updated_by',

            // ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

    
        <?php 
            $form = ActiveForm::begin(
                        [
                        'enableClientValidation'=>false,
                        'options' => [
                                'id' => 'plan-status-form'
                             ]
                        ]
                    ); 


            $list = [0=>"Approve", 1=>"Request Clarification", 2=>"Reject"];

            echo $form->field($model, 'plan_status')->dropDownList($list, ['id' => 'plan-status'])->label("Update plan status");

            $html = '<section class="form-group status-request-clarification" style="display: block;">';
            $html .= $form->field($model, 'notes')->textarea(['rows' => 2, "placeholder" => "i.e. Photo taken not clear. Please take again."])->hint("Hint: Time of entry will be auto generated");
            $html .= '</section>';

            $html .= '<section class="form-group status-reject" style="display: block;">';
            $html .= $form->field($model, 'notes')->textarea(['rows' => 2, "placeholder" => "i.e. Enter reject reasons"])->hint("Hint: ???");
            $html .= '</section>';

            echo $html;        


            echo Html::submitButton(Yii::t('backend', 'Update'), ['class' => 'submit-btn btn btn-primary']);


            ActiveForm::end();
        ?>
    
    



</div>


<?php




$script = <<< JS

$(document).ready(function () {

    $('#plan-status').on('change', function (e) {
        $("section.form-group").not(".field-plan-status").hide();
        checkStatusField();
    });
    function checkStatusField() {
        $('#plan-status-form').off('beforeSubmit');
        var val = $('#plan-status').val();
        $(".submit-btn").html("Approve");    
        var prompt = "???";

        if (val == 0 ) {
            //approve
            prompt = "Do you really want to approve?";
            $(".submit-btn").html("Approve");   
            
        } else if (val == 1 ) {
            //request clarification
            $("section.status-request-clarification").show();
            prompt = "Do you really want to proceed?";
            $(".submit-btn").html("Request clarification");   
            
        } else if (val == 2 ) {
            //reject
            $("section.status-reject").show();
            prompt = "Do you really want to reject?";
            $(".submit-btn").html("Request clarification");   
            
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


<?php 

/*

    <?php if ($model->plan_status == InstapPlanPool::STATUS_PENDING_APPROVAL): ?>
    <p>
        <?= Html::a(Yii::t('backend', 'Approve'), ['approve', 'id' => $model->id], [
            'class' => 'btn btn-success',
            'data' => [
                    'confirm' => Yii::t('backend', 'Are you sure you want to approve this item?'),
                    'method' => 'post',
                ],
            ])
        ?> 
        <?= Html::a(Yii::t('backend', 'Reject'), ['reject', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                    'confirm' => Yii::t('backend', 'Are you sure you want to reject this item?'),
                    'method' => 'post',
                ],
            ])
        ?> 
    </p>

    <hr/>

    <p>

        <?php $form = ActiveForm::begin(['id' => 'request-clarification', 'action'=> Url::to(['request-clarification', "id" => $model->id])]); ?>

        <?= $form->field($model, 'notes')->textarea(['rows' => 2, "placeholder" => "i.e. Photo taken not clear. Please take again."])->hint("Hint: Time of entry will be auto generated") ?>

        <div class="form-group">
            <?= Html::submitButton(Yii::t('backend', 'Request Clarification'), ['class' => 'btn btn-primary']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </p>
    <?php endif; ?>


    */

    ?>


