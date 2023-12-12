<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;
use kartik\select2\Select2;
use common\models\User;



/* @var $this yii\web\View */
/* @var $model common\models\User */

$this->title = Yii::t('backend', 'Add InstaProtection Staff');
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'InstaProtection Staffs'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-create">

    <?php $form = ActiveForm::begin(); ?>

    <?php
    
        $html = $form->field($modelIpStaff, 'user_id')->widget(Select2::classname(), [
            'data' => User::getUserNotIpStaffConcatWithUserName(),
            'options' => ['placeholder' => 'Enter full mobile number ...'],
            'pluginOptions' => ['allowClear' => true],
        ])->label('User Mobile Number');

    
        //$form->field($model, 'role')->radioList(User::ipStaffRoles()); 
        $html .= $form->field($modelIpStaff, 'email_admin')->textInput();
        $html .= $form->field($modelIpStaff, 'password')->passwordInput();
        $html .= $form->field($modelIpStaff, 'role_arr')->checkBoxList(User::ipStaffRoles());
        $html .= "<br>";
        $html .= $form->field($modelIpStaff, 'permission_arr')->checkBoxList(User::countryAccessPermissions());
        echo $html;
    ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('backend', 'Save'), ['class' => 'btn btn-success' , 'style'=>'margin-top: 25px;']) ?>
    </div>

    <?php ActiveForm::end(); ?> 

</div>
