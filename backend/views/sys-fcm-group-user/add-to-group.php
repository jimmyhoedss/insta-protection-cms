<?php 

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use common\models\SysFcmGroup;
use common\models\SysFcmGroupUser;

/* @var $this yii\web\View */
/* @var $model common\models\FcmToken */
/* @var $form ActiveForm */

$this->title = 'Add ' . utf8_decode($userProfile->nickname) .' To A Message Group';

?>

<div class="sys-fcm-group-user-add-to-group">
    <?php $form = ActiveForm::begin(); ?>
        <?php     
            echo $form->field($model, 'fcm_group_id')->dropDownList(SysFcmGroupUser::groupNameAndNumberOfUser()); 
        ?>
    
        <div class="form-group">
            <?php 
                echo Html::submitButton('Add', [
                    'class' => 'btn btn-primary'
                ]); 
            ?>
        </div>
    <?php ActiveForm::end(); ?>

</div>