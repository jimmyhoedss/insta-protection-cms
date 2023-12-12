<?php

use yii\helpers\Html;
use common\components\MyCustomActiveRecord;
use yii\bootstrap\ActiveForm;
use common\models\UserProfile;
use common\models\User;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model common\models\User */
$fullName = $model->userProfile->first_name . $model->userProfile->last_name;
$this->title = Yii::t('backend', 'User: {name}', [
    'name' => $fullName
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Users'), 'url' => ['index']];
$this->params['breadcrumbs'][] = Yii::t('backend', 'Update');

$link = Url::to(['user/view', 'id' => $model->id]);
?>

    <div class="user-update">
    <div class="">
        <div class="box-body">
			<div class="user-form">

			    <?php $form = ActiveForm::begin(); ?>
			        <?php 
			       		$html = "";
			            $html .= $form->field($model, 'account_status')->dropDownList(User::accountStatus())->hint("Remember to send a [Expire User Login Token] message when suspending user.");
			            $html .= $form->field($model, 'email_status')->dropDownList(User::emailStatus())->hint("Verify user email to enable register plan");
			            $html .= $form->field($model, 'mobile_status')->dropDownList(User::mobileStatus());
			            $html .= "<hr>";
				    	$html .= $form->field($model, 'notes')->textarea(['rows' => 6, 'placeholder'=> 'Enter notes for this user.'])->label("Manager's notes");
				    	echo $html;				
			        ?>
			        <div class="form-group">
			            <?php echo Html::submitButton(Yii::t('backend', 'Save'), ['class' => 'btn btn-primary']) ?>
			        </div>
			        <?php 
			            //echo $form->errorSummary($model); 
			        ?>
			        
			    <?php ActiveForm::end(); ?>

			</div>
		</div>
	</div>

</div>

