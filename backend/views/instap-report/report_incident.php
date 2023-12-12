<?php
	use common\models\InstapPlanPool;
	use common\models\UserCaseAction;
	use common\models\UserCase;
	use common\models\UserCaseActionDocument;
	use common\components\Utility;
    
    $planPool = InstapPlanPool::find()->where(['id'=>$id])->one();
    $user = $planPool->user;
    $userProfile = $planPool->userProfile;
    $userPlan = $planPool->userPlan;
    $details = $planPool->userPlan->details;
    $userCase = $planPool->userCase;

    $userCaseResubmit = UserCaseAction::find()->where(['case_id'=>$userCase->id])->andWhere(['action_status'=>UserCaseAction::ACTION_CLAIM_REGISTRATION_RESUBMIT])->orderBy(['created_at'=>SORT_DESC])->asArray()->all();

    $userCaseAction = UserCaseAction::find()->where(['case_id'=>$userCase->id])->andWhere(['action_status'=>UserCaseAction::ACTION_CLAIM_UPLOAD_PHOTO])->one();

    //if have action resubmit check whether have image attached on the action or not
    if($userCaseResubmit){
    	$documents = null;
    	//loop through all the resubmit action id and get the latest one that have image attached on it
    	$caseIds = array_column($userCaseResubmit, "id");
    	foreach ($caseIds as $id) {
	    	$documents = UserCaseActionDocument::find()->where(['case_action_id'=>$id])->all();
	    	if($documents) {
	    		break;
	    	}
	    }
	    //if no image found in resubmit action then take the image from claim upload photo action.
    	if(empty($documents)){
    		$documents = UserCaseActionDocument::find()->where(['case_action_id'=>$userCaseAction->id])->all();
    	}
    }else{
    	$documents = UserCaseActionDocument::find()->where(['case_action_id'=>$userCaseAction->id])->all();
    }
    
?>
<div class="pdf-container">
	<div style="width: 20%; margin-bottom:20px;">
		<img style="width: 100%" src="./img/ip_logo.png"/>
	</div>
	<div class="title-container text-center text-uppercase" style="margin-bottom: 10px">
		<h3><strong><?=Yii::t('backend','INCIDENT REPORT')?></strong></h3>
	</div>
	<div class="table-container">
		<table width="100%" style="border-bottom: 1px solid black; border-collapse: collapse;">
			<thead>
				<tr>
					<th colspan="2" class="text-center" style="border-bottom: 1px solid black;">
						<h4><strong><?=Yii::t('backend','Customer Details')?></strong></h4>
					</th>
					<th colspan="2" class="text-center" style="border-bottom: 1px solid black;">
						<h4><strong><?=Yii::t('backend','Device Details')?></strong></h4>
					</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td width="13%" style="font-size: 12px; padding: 5px 0px;">
						<?=Yii::t('backend','Name')?>
					</td>
					<td width="37%" style="font-size: 12px;">
						: <?= $user->userProfile->fullName ?>
					</td>
					<td width="13%" style="font-size: 12px;">
						<?=Yii::t('backend','Claim ID')?>
					</td>
					<td width="37%" style="font-size: 12px;">
						: <?= UserCase::formUpClaimNumber($userCase)?>
					</td>
				</tr>
				<tr>
					<td style="font-size: 12px; padding: 5px 0px;">
						<?=Yii::t('backend','Card Serial No')?>
					</td>
					<td style="font-size: 12px;">
						: <?= $planPool->policy_number ?>
					</td>
					<td style="font-size: 12px;">
						<?=Yii::t('backend','Brand')?>
					</td>
					<td style="font-size: 12px;">
						: <?= $details->sp_brand ?>
					</td>
				</tr>
				<tr>
					<td style="font-size: 12px; padding: 5px 0px;">
						<?=Yii::t('backend','Model')?>
					</td>
					<td style="font-size: 12px;">
						: <?= $details->sp_model_number ?>
					</td>
					<td style="font-size: 12px;">
						<?=Yii::t('backend','Serial No')?>
					</td>
					<td style="font-size: 12px;">
						: <?= $details->sp_serial ?>
					</td>
				</tr>
				<tr>
					<td style="font-size: 12px; padding: 5px 0px;">
						<?=Yii::t('backend','Email')?>
					</td>
					<td style="font-size: 12px;">
						: <?= $user->email ?>
					</td>
					<td style="font-size: 12px;">
						<?=Yii::t('backend','IMEI')?>
					</td>
					<td style="font-size: 12px;">
						: <?= $details->sp_imei ?>
					</td>
				</tr>
				<tr>
					<td style="font-size: 12px; padding: 5px 0px;">
						<?=Yii::t('backend','Phone')?>
					</td>
					<td style="font-size: 12px;">
						: <?= "+".$user->mobile_number_full ?>
					</td>
					<td style="font-size: 12px;">
						<?=Yii::t('backend','Purchase Date')?> 
					</td>
					<td style="font-size: 12px;">
						: <?= $details->sp_device_purchase_date ?>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="description-container" style="border-bottom: 1px solid black; padding-bottom: 10px;">
		<div class="description-title text-left">
			<h4><strong><?=Yii::t('backend','Description Nature Of Damage')?></strong></h4>
		</div>
		<div class="description-text">
			<?= date("d/m/Y h:mA", $userCase->occurred_at) . " at " . $userCase->location ?>
			<br>
			<?= $userCase->description ?>
		</div>
	</div>
	<div class="proof-container" style="border-bottom: 1px solid black; padding-bottom: 10px;">
		<div class="proof-title text-left">
			<h4><strong><?=Yii::t('backend','Proof of Damage Device')?></strong></h4>
		</div>
		<div class="proof-image">
			<?php
				foreach ($documents as $document) {
					$link = Utility::getPreSignedS3Url($document->thumbnail_path);
					echo '<img style="width: 30%; height:auto; padding-right: 10px;" src="'.$link.'">';
				}
			?>
		</div>
	</div>
	<div class="footer" style="padding-top: 10px; font-size: 12px;">
		<br>
		<br>
		<div class="container">
			<div class="row">
				<p><?=Yii::t('backend','Signature:')?></p>
				<p>Date: <?= date("Y-m-d", time()) ?></p>
			</div>
		</div>
		
	</div>
</div>