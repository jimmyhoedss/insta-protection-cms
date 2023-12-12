<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use kartik\select2\Select2;
use common\models\User;
use common\models\DealerCompany;
use common\models\DealerUser;

?>

<div class="dealer-user-form">

    <?php 

        $users = DealerUser::getUserNotInAnyCompanyConcatWithUserName();
        $c = DealerCompany::find()->andWhere(["region_id"=>Yii::$app->session->get('region_id')])->active()->all();


        $widget_config1 = [
                'data' => ArrayHelper::map( $c , 'id', 'business_name'),
                'options' => ['placeholder' => 'Select Dealer Company ...'],
                'pluginOptions' => [
                    'allowClear' => true ],
                ];

        $widget_config2 = [
                'data' => $users,
                'options' => ['placeholder' => 'Select a user ...'],
                'pluginOptions' => [
                    'allowClear' => true ],
                ];



        $form = ActiveForm::begin(); 

        $html = $form->field($model, 'dealer_company_id')->widget(Select2::classname(), $widget_config1)->label('Dealer Company');

        $html .= $form->field($model, 'user_id')->widget(Select2::classname(), $widget_config2)->label('User Mobile Number'); 

        $html .= $form->field($model, 'notes')->textarea(['rows' => 6]); 

        $html .= $form->field($model, 'roles')->radioList( User::dealerUserRoles() ,['separator'=>'<br>']); 

        echo $html;
    ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('backend', 'Save'), ['class' => 'btn btn-success' , 'style'=>'margin-top: 25px;', 'name' => 'submitButton']) ?>
    </div>

    <?php ActiveForm::end(); ?> 

</div>
