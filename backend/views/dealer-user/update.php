<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use kartik\select2\Select2;
use common\models\User;
use common\models\DealerCompany;
use common\models\DealerUser;
$name = $model->userProfile->first_name ." ". $model->userProfile->last_name;
$this->title = Yii::t('backend', 'Update Staff', [
    'name' => $name,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Staffs'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $name, 'url' => ['view', 'id' => $model->user_id]];
$this->params['breadcrumbs'][] = Yii::t('backend', 'Update');
?>

<div class="dealer-user-update">

    <?php 

        $form = ActiveForm::begin();

        $html = $model->userProfile->getUserDetailLayout();

        $html .= $form->field($model, 'user_id', ['template' => '{input}'])->hiddenInput(['value' => $model->user_id]);

        $html .= $form->field($model, 'dealer_company_id')->widget(Select2::classname(), [
            'data' => ArrayHelper::map(DealerCompany::find()->andWhere(["region_id"=>Yii::$app->session->get('region_id')])->active()->all(), 'id', 'business_name'),
            'options' => ['placeholder' => 'Select Dealer Company ...'],
            'pluginOptions' => [
                'allowClear' => true ],
            ])->label('Dealer Company');

        // $html .= $form->field($model, 'user_id')->text; 

   

        $html .= $form->field($model, 'notes')->textarea(['rows' => 6]);

        $html .= $form->field($model, 'roles')->radioList( User::dealerUserRoles() ,['separator'=>'<br>']); 

        echo $html;
    ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('backend', 'Save'), ['class' => 'btn btn-success' , 'style'=>'margin-top: 25px;', 'name' => 'submitButton']) ?>
    </div>

    <?php ActiveForm::end(); ?> 

</div>
  