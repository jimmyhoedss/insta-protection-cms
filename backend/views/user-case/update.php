<?php

use yii\helpers\Html;
use common\models\UserCaseAction;
use common\models\UserCaseActionDocument;
use yii\helpers\Url;
use yii\widgets\DetailView;
use kartik\grid\DataColumn;
use kartik\grid\GridView;
use common\models\UserProfile;
use common\models\UserCase;
use common\models\UserCaseRepairCentre;
use common\models\QcdRepairCentre;
use common\models\User;
use yii\widgets\ActiveForm;
use common\components\MyCustomActiveRecord;
use common\components\Utility;
use yii\web\JsExpression;
use trntv\filekit\widget\Upload;
use yii\helpers\ArrayHelper;
use yii\bootstrap\Tabs;
use common\widgets\MyUpload\MyUpload;



/* @var $this yii\web\View */
/* @var $model common\models\UserCase */
$this->title = Yii::t('backend', "Claim #") . str_pad($model->id, 4, "0", STR_PAD_LEFT);  
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Policy Claims'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $this->title];
?>
<div class="user-case-update">

	<div class="box-body">

	    <h4 class="sub-title"><?=Yii::t('backend', "Policy Details")?></h4>
	    <?php echo $model->planPool->getPolicyDetailLayout(); ?>
	    <hr/>

		<h4 class="sub-title"><?=Yii::t('backend', "User Details")?></h4>
		<?php echo $model->planPool->user->userProfile->getUserDetailLayout(); ?>

		<hr/>
    	<h4 class="sub-title"><?=Yii::t('backend', "Selected Repair Center")?></h4>
    	<div>
    		<?php 
    		// print_r(UserCaseRepairCentre::getRepairCentreDetails($model->caseRepairCentre->repair_centre_id));exit();
	    		if(isset($model->caseRepairCentre->repair_centre_id)) {
	    			$repairCenter = QcdRepairCentre::find()->where(['id' => $model->caseRepairCentre->repair_centre_id ])->one();
	    			echo "<b><i>".$repairCenter->repair_centre."</i></b><br>";
	    			echo "<p><i> ".$repairCenter->address."</i></p>";
	    		} else {
	    			echo "No repair center found";
	    		}
    			
    		?>
    	</div>

    	<hr/>
    	<h4 class="sub-title"><?=Yii::t('backend', "Claim Description")?></h4>
    	<div class="jumbotron">
    		<p>
    			<?php
    				echo $model->description;
    				echo "<br>";
    			?>
	    		<span style="font-size: 15px;">
					<?php 
						$clarificationNum = 1;
						foreach($dataProvider->query->all() as $row) {
							if($row["action_status"] == UserCaseAction::ACTION_CLAIM_REGISTRATION_RESUBMIT){
								$notes_user = $row["notes_user"] == "" ? "-" : $row["notes_user"];
								echo "<br>Clarification " . ($clarificationNum) . ": " . $notes_user;
								$clarificationNum ++;
							}
						}
					?>
				</span>
			</p>
		</div>

    	<hr/>
    	<h4 class="sub-title"><?=Yii::t('backend', "Claim Activities")?></h4>


	    <?php echo GridView::widget([
	        'dataProvider' => $dataProvider,
	        // 'filterModel' => $searchModel,
	        'layout' => '{items}{pager}',
	        'columns' => [
	            ['class' => 'yii\grid\SerialColumn',
	            'headerOptions' => ['width' => '20px']
	        	],
	            [
	                'label' => Yii::t('backend', "User"),
	                'format' => 'raw',
	                'value' => function($model) { 
	                    $u = User::find()->andWhere(['id'=>$model->created_by])->one();
	                    $link = Url::to(['user/view', 'id' => $model->created_by]);
	                    $html = $u->userProfile->getAvatarSmallLayout($link);
	                    return $html;	                 
	                },
	                'headerOptions' => ['width' => '220px'],
	            ],
	            [
	                'label' => Yii::t('backend', "Activity"),
	                'format' => 'raw',
	                'attribute' => 'action_status',
	                'value' => function($model) {
	                    $s = UserCaseAction::allActionStatus()[$model->action_status];
	                    return $s;
	                }, 
	                'headerOptions' => ['width' => '150px'],

	            ],
	            [
	                'attribute'=>'notes_user',
	                'format' => 'html',
	                'value' => function($model) {
	                    $html = (($model->action_status ==UserCaseAction::ACTION_CLAIM_CLOSED && $model->userCase->cost_repair != null) ? "Cost repair: " : "" ) . $model->notes_user;

                        $arr = UserCaseActionDocument::find()->andWhere(['case_action_id'=> $model->id])->asArray()->all();
                        
                        $html .= "<div class = 'case-doc-holder'>";
                        $html .= UserCaseActionDocument::getDocumentLayoutByModel($arr);
                        $html .= "</div>";


                        // $html .= UserCaseActionDocument::loopDocumentByType($model->id,UserCaseActionDocument::TYPE_QUOTATION);
                        // foreach ($arr as $key => $value){
                        //     $base = $value['thumbnail_base_url'];
                        //     $path = $value['thumbnail_path'];
                        //     // $link = $base . "/" . $path ;
                        //     $link = Utility::getPreSignedS3Url($path);
                        // 	if($value['type'] == UserCaseActionDocument::TYPE_SERVICE_REPORT || $value['type'] == UserCaseActionDocument::TYPE_DISCHARGE_VOUCHER) {
                        // 		$html .= "<a href=".$link." target='_blank'>PDF</a> ";
                        // 	}else{
                        //     	$html .= "<a href=".$link." target='_blank'><img class='photo x-small' src='". $link."'></a> ";
                        // 	}
                        // }

                        if($model->action_status == UserCaseAction::ACTION_CLAIM_PROCESSING){
                        	$doc = UserCaseActionDocument::findOne(['case_action_id'=>$model->id]);
                        	// $html .= UserCaseActionDocument::documentByType($doc, 'pdf');
	                		// $html .= Html::a('<i class="fa fa-file-export"> Generate Incident Report</i>', '/report/incident?id='. $model->userCase->plan_pool_id, ['class' => 'btn btn-success']);
	                		// $html .= "<br><br>";
	                	}

	                    return $html;	                	
	                },                 
	                'headerOptions' => ['width' => '*'],
	            ],
	            [
	                'attribute' => 'notes',
	                'format' => 'raw',
	                'value' => function($model) {
                    	return $model->notes;
	                },
	                'contentOptions' => ['style' => 'padding-top:2px'],
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

		$list = UserCaseAction::processClaimActionStatus();
		$form = ActiveForm::begin([
			'enableClientValidation'=>false, 
			'options' => [ 'id' => 'case-status-form'],
		]); 
		echo $form->errorSummary($model); 
        echo $form->errorSummary($claimActionForm);
        echo $form->errorSummary($modelAction);

		$html = $form->field($modelAction, 'action_status')->dropDownList($list, ['id' => 'case-status', 'prompt' => ['text'=> '--Select--', 'options'=> ['disabled' => true, 'selected' => true]]])->label("Update Claim Status");
		$html .= '<section class="form-group case-note" style="display: block;">';
		$html .= $form->field($modelAction, 'notes_user')->textarea(['rows' => 4, 'maxlength'=>256, 'placeholder' => "No more than 256 characters"]);
		$html .= '</section>';
		$html .= $form->field($modelAction, 'notes')->textarea(['rows' => 4, 'maxlength'=>256, 'placeholder' => "No more than 256 characters"]);

		$html .= '<section class="form-group case-close" style="display: block;">'; 

		$html .= '<div id="supporting-document">';
		$html .= $form->field($modelCase, 'cost_repair')->textInput(['type' => 'number','placeholder' => "Cost price in numeric",'step'=>'any']);


		    /*document start*/
	        $html .=  Tabs::widget([
	        	'options' => ['style' => "margin-bottom:2px;"],
			    'items' => [
			        [
			            'label' => 'Pre photo',
			            'content' => $form->field($claimActionForm, 'photo_pre')->widget(
				            MyUpload::className(),
				            [
				                'url' => ['/file-storage/upload'],
				                'maxFileSize' => 5000000, // 5 MiB
				                'uploadPath' => 'media/case/claim_close/img',
				                'maxNumberOfFiles' => 3,
				                'acceptFileTypes' => new JsExpression('/(\.|\/)(jpe?g|png)$/i'),
			            		'options' => ['id' => 'prePhotoField'],
				            ])->label("")->hint("<i>Pre photo</i>"),
			        ],
			        [
			            'label' => 'Post photo',
			            'content' => $form->field($claimActionForm, 'photo_post')->widget(
				            MyUpload::className(),
				            [
				                'url' => ['/file-storage/upload'],
				                'maxFileSize' => 5000000, // 5 MiB
				                'uploadPath' => 'media/case/claim_close/img',
				                'maxNumberOfFiles' => 3,
				                'acceptFileTypes' => new JsExpression('/(\.|\/)(jpe?g|png)$/i'),
			            		'options' => ['id' => 'postPhotoField'],
				            ])->label("")->hint("<i>Post photo</i>"),
			        ],
			        [
			            'label' => 'Quotation',
			            'content' => $form->field($claimActionForm, 'quotation', [ 'options' => [ 'style' => 'color: red']])->widget(
				            MyUpload::className(),
				            [
				                'url' => ['/file-storage/upload-pdf'],
				                'maxFileSize' => 5000000, // 5 MiB
				                'uploadPath' => 'media/case/claim_close/pdf',
				                'maxNumberOfFiles' => 3,
				                'acceptFileTypes' => new JsExpression('/(\.|\/)(pdf)$/i'),
			            		'options' => ['id' => 'quotationField'],	
				            ])->label("")->hint("<i>Submit in pdf</i>"),
			            'active' => true,
			        ],
			        [
			            'label' => 'Service report',
			            'content' => $form->field($claimActionForm, 'service_report')->widget(
				            MyUpload::className(),
				            [
				                'url' => ['/file-storage/upload-pdf'],
				                'maxFileSize' => 5000000, // 5 MiB
				                'uploadPath' => 'media/case/claim_close/pdf',
				                'maxNumberOfFiles' => 3,
				                'acceptFileTypes' => new JsExpression('/(\.|\/)(pdf)$/i'),
			            		'options' => ['id' => 'serviceReportField'],
				            ])->label("")->hint("<i>Submit in pdf</i>"),
			        ],
			        [
			            'label' => 'Discharge voucher',
			            'content' => $form->field($claimActionForm, 'discharge_voucher')->widget(
				            MyUpload::className(),
				            [
				                'url' => ['/file-storage/upload-pdf'],
				                'maxFileSize' => 5000000, // 5 MiB
				                'uploadPath' => 'media/case/claim_close/pdf',
				                'maxNumberOfFiles' => 3,
				                'acceptFileTypes' => new JsExpression('/(\.|\/)(pdf)$/i'),
			            		'options' => ['id' => 'dischargeVoucherField'],
				            ])->label("")->hint("<i>Submit in pdf</i>"),
			        ],
			    ],
			]);
		$html .= "</div>";

        $html .= '<hr>';
		$html .= $form->field($claimActionForm, 'flag_skip_doc')->checkBoxList(['1'=>'To close out-of-coverage cases, check this to skip uploading the required documents'],['id' => 'flagSkipDoc'])->label('For "out-of-coverage" cases');
		$html .= '</section>';

		/*document end*/

		$html .= '<div class="form-group">';
		$html .= Html::submitButton(Yii::t('backend', 'Save'), ['class' => 'btn btn-success submit-btn']);
		$html .= '</div>';
		if($model->current_case_status !== UserCase::CASE_STATUS_CLAIM_CANCELLED && $model->current_case_status !== UserCase::CASE_STATUS_CLAIM_REJECTED) {
			echo $html;
		}

		ActiveForm::end();

	?>

	</div>

</div>

<?php

//actions that requires notes
$arr = array_keys(UserCaseAction::requireNotesActionStatus());
$str =  Utility::enclose_quotes_str($arr);
$caseStatus = json_encode(UserCaseAction::allActionStatus());
$caseStatusClose = json_encode(UserCaseAction::ACTION_CLAIM_CLOSED);

$script = <<< JS

const arr = [{$str}];
const caseStatus = {$caseStatus};
const caseStatusClose = {$caseStatusClose};

$(document).ready(function () {
	
	$('#flagSkipDoc input:checkbox').change(function(){
	    if($(this).is(':checked')){
	        confirm("Do you really want to skip uploading the required documents?");
	        $("#supporting-document").hide();
	    } else {
	    	$("#supporting-document").show();
	    }
	});

    $('#case-status').on('change', function (e) {
        $("section.form-group").not(".field-plan-status").slideUp();
        checkStatusField();
    });

    function checkStatusField() {
        $('#case-status-form').off('beforeSubmit');
        var val = $('#case-status').val(); 
        var prompt = "Do you really want to ["+ caseStatus[val] +"]";

        if (arr.indexOf(val) != -1) {
        	$("section.case-note").slideDown();
	        $('#case-status-form').on('beforeSubmit', function (e) {
	            if (!confirm(prompt)) {
	                return false;
	            }
	            return true;
	        });
        } else if(val == caseStatusClose) {
            $("section.case-close").slideDown();
            $('#case-status-form').on('beforeSubmit', function (e) {
	            if (confirm(prompt)) {
	            	if(allDocumentsUploaded()){
	                	return true;
	            	} else {
	            		if(confirm("Do you really want to skip uploading the required documents?")){
	            			return true;
	            		}
	            	}
	            }
	            return false;
	        });
        }
    }

    function allDocumentsUploaded(){
    	let fields = ['#quotationField', '#prePhotoField', '#postPhotoField', '#serviceReportField', '#dischargeVoucherField'];
    	for (field in fields){
    		if(!isUploaded(fields[field])){
    			return false;
    		}
    	}

    	return true;
    }

    function isUploaded(element){
    	var attr = $(element).attr('value');
		return (typeof attr !== typeof undefined && attr !== false);
    }

    $('#case-status').trigger('change');
});

JS;
$this->registerJs($script);

?>
