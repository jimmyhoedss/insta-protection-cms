<?php

use yii\helpers\Html;
use common\models\User;
use yii\bootstrap\ActiveForm;
use trntv\filekit\widget\Upload;

/* @var $this yii\web\View */
/* @var $model common\models\User */
/* @var $roles yii\rbac\Role[] */

$this->title = "Account"
        

?>
<div class="user-update">
    <div>
        <div class="box-body">
            <div class="user-form">

                <?php $form = ActiveForm::begin(); ?>
                    <?php 
                        echo $form->field($model, 'email_admin')->textInput();
                        echo $form->field($model, 'password')->passwordInput();         
                    ?>
                    <div class="form-group">
                        <?php echo Html::submitButton(Yii::t('backend', 'Save'), ['class' => 'btn btn-primary', 'name' => 'signup-button']) ?>
                    </div>
                    <?php 
                        //echo $form->errorSummary($model); 
                    ?>
                    
                <?php ActiveForm::end(); ?>

            </div>
        </div>
    </div>

</div>
