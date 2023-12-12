<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\authclient\widgets\AuthChoiceAsset;
use frontend\assets\LoginAsset;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $model \frontend\modules\user\models\SignupForm */

$this->title = Yii::t('frontend', 'Registration');
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-registration">

    <div class="container container-sm">
        <div class="overview">
            <br>
            <h3 class="menu-title menu-title-tight "><?php echo Html::encode($this->title) ?></h3>
            <p>Enter your basic information to get started.</p>

            <div class="form-holder">
            <?php $form = ActiveForm::begin(['id' => 'form-registration']); ?>

                    <div class="form-group">

                <?php 
                //echo $form->field($model, 'username');
                ?>
                <?php 
                    echo "<label>INFO</label>";
                    echo "<div class='row'><div class='col-sm-6'>";
                    echo $form->field($model, 'nickname')->textInput(['placeholder' => $model->getAttributeLabel('nickname')])->label(false);
                    echo "</div><div class='col-sm-6'>";
                    echo $form->field($model, 'password')->passwordInput(['placeholder' => $model->getAttributeLabel('password')])->label(false);
                    echo "</div></div>";
                    echo "<div class='row'><div class='col-sm-6'>";
                    echo $form->field($model, 'email')->textInput(['placeholder' => $model->getAttributeLabel('email')])->label(false);
                    echo "</div><div class='col-sm-6'>";
                    echo $form->field($model, 'age_group')->dropDownList([ 'group_1' => 'Group 1', 'group_2' => 'Group 2', 'group_3' => 'Group 3'], ['prompt' => 'Choose your age group'])->label(false);
                ?>
                
                <br>
                </div>
                <div class="form-group text-center">
                    <?php echo Html::submitButton(Yii::t('frontend', 'Register'), ['class' => 'btn submit-btn btn-custom ', 'name' => 'signup-button']) ?>
                </div>
                    

                
            <?php ActiveForm::end(); ?>
            </div>
            
            
                <p class="bottom-text small text-center">
                    <?php 
                    echo Yii::t('frontend', 'If you forgot your password you can reset it <a href="{link}">here</a>', [
                        'link'=>yii\helpers\Url::to(['user/request-password-reset'])
                    ])                     
                    ?>
                    <br>
                    <br>
                    <?php

                     echo Yii::t('frontend', 'By proceeding, I agree that you can collect, use and disclose the information provided by me in accordance with your <a href="{link}">Privacy Policy</a> which I have read and understand.', [
                        'link'=>yii\helpers\Url::to(['/privacy'])
                    ]) ?>
                </p>
            <br> 
        </div>
    </div>
</div>

