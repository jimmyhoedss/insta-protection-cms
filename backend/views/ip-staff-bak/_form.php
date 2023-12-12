<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use common\models\User;

/* @var $this yii\web\View */
/* @var $model common\models\User */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="user-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= 
        //LOYNOTE:: Don't put logic in view, try to put in model to maximise reuse. Refactor this!

        $form->field($modelIpStaff, 'user_id')->widget(Select2::classname(), [
            'data' => ArrayHelper::map(User::find()->join('LEFT JOIN','rbac_auth_assignment','rbac_auth_assignment.user_id = id')->andWhere(["region_id"=>Yii::$app->session->get('region_id')])->andWhere(['not', ['rbac_auth_assignment.item_name' => [User::ROLE_ADMINISTRATOR, User::ROLE_DEALER_MANAGER, User::ROLE_DEALER_ASSOCIATE, User::ROLE_IP_ADMINISTRATOR, User::ROLE_IP_MANAGER, User::ROLE_IP_ADMIN_ASSISTANT]]])->all(), 'id', 'mobile_number_full'),
            'options' => ['placeholder' => 'Enter full mobile number ...'],
            'pluginOptions' => ['allowClear' => true],
        ])->label('User Mobile Number');
    ?>

    <!-- <?= $form->field($modelIpStaff, 'notes')->textarea(['rows' => 6]) ?>  -->

    <?php
        //$form->field($model, 'role')->radioList(User::ipStaffRoles()); 
        echo $form->field($modelIpStaff, 'role_arr')->checkBoxList(User::ipStaffRoles());
        echo "<br>";
        echo $form->field($modelIpStaff, 'permission_arr')->checkBoxList(User::countryAccessPermissions());
    ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('backend', 'Save'), ['class' => 'btn btn-success' , 'style'=>'margin-top: 25px;']) ?>
    </div>

    <?php ActiveForm::end(); ?> 

</div>
