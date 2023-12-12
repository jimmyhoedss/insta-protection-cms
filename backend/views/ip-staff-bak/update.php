<?php

use yii\helpers\Html;
use common\components\MyCustomActiveRecord;
use yii\bootstrap\ActiveForm;
use common\models\UserProfile;
use common\models\User;
use yii\helpers\Url;


$this->title = Yii::t('backend', 'Update');
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'InstaProtection Staffs'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$link = Url::to(['user/view', 'id' => $model->id]);
?>

    <div class="user-update">
    <div class="">
        <div class="box-body">
			<div class="user-form">
				<h4 class='sub-title'>User Details</h4>
			    <?php 
			    	$html = $model->userProfile->getUserDetailLayout();
			    	$html .= "<hr><br>";
		    		$form = ActiveForm::begin(); 
		        	//Loynote: can we have admin & manager role concurrently?
	            	//echo $form->field($model, 'role')->radioList(User::ipStaffRoles());
		        	$html .= $form->field($modelIpStaff, 'role_arr')->checkBoxList(User::ipStaffRoles());
	            	$html .= "<br>";
	            	$html .=  $form->field($modelIpStaff, 'permission_arr')->checkBoxList(User::countryAccessPermissions());
	            	echo $html;
		        ?>
		        <div class="form-group">
		            <?php echo Html::submitButton(Yii::t('backend', 'Save'), ['class' => 'btn btn-primary' , 'style'=>'margin-top: 25px;']) ?>
		        </div>

			    <?php ActiveForm::end(); ?>

			</div>
		</div>
	</div>

</div>
