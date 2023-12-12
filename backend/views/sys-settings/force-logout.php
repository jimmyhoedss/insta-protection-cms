<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\log\Logger;
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;

use common\components\MyCustomActiveRecord;
use common\models\UserProfile;
use common\models\DealerCompany;
use common\models\User;

use kartik\select2\Select2;

$this->title = Yii::t('backend', 'Force Logout');



?>

<div class="box">
    <div class="box-body">
        <h4 class="sub-title">Force Logout All Users</h4>
        <?php                    

                $html = Html::a('<i class="fa fa-sign-out-alt"></i>
                <span class="label label-default">Force Logout ALL users
                </span>',
                    ['/sys-settings/force-logout-all'],
                    [
                        'title' => 'Force Logout ALL Users',
                        'data' => [
                            'confirm' => Yii::t('backend', 'Force logout ALL users?'),
                            'method' => 'post',
                            'params' => [
                                '_get'=> $_GET,
                                'controller'=>Yii::$app->controller->id,
                                'action'=>Yii::$app->controller->action->id,
                                'target' => \backend\controllers\SiteController::FORCE_LOGOUT_TARGET_SYSTEM,
                            ],
                        ],
                    ]
                );
                $html .= "<br><br><br>";
                echo $html;
        ?>

    <!-- force logout individual -->
      
        <h4 class="sub-title">Force Logout By Individual</h4>
            <?php 
                // $u = User::find()->active()->all();
                $arr = ArrayHelper::map( $user_arr, 'id', 'mobile_number_full');
                $widget_config =   [
                                'data' => $arr,
                                'options' => ['placeholder' => 'Select Mobile Number ...'],
                                'pluginOptions' => [ 'allowClear' => true ],
                            ];

                $html ="";
                $form = ActiveForm::begin(['action' => ['sys-settings/force-logout'],'options' => ['method' => 'post']]); 
                $html .= $form->field($model, 'user_id')->widget( Select2::classname(), $widget_config)->label('User Mobile Number'); 
                echo $html;
            ?>
            <div class="form-group">
                <?php echo Html::submitButton(Yii::t('backend', 'Force Logout'), ['class' => 'btn btn-primary']) ?>
            </div>
            <br><br>
            <?php ActiveForm::end(); ?>

    <!-- force logout individual end -->    




    <!-- force logout by company -->
    
            <h4 class="sub-title">Force Logout By Company</h4>
            <?php 
                $arr = ArrayHelper::map( $company_arr, 'id', 'business_name');
                $widget_config =   [
                                'data' => $arr,
                                'options' => ['placeholder' => 'Select Company ...'],
                                'pluginOptions' => [ 'allowClear' => true ],
                            ];
                $html ="";
                $form = ActiveForm::begin(['action' => ['sys-settings/force-logout'],'options' => ['method' => 'post']]); 
                $html .= $form->field($model, 'dealer_company_id')->widget(Select2::classname(), $widget_config
                            )->label('Company');

                echo $html;
            ?>
            <div class="form-group">
                <?php echo Html::submitButton(Yii::t('backend', 'Force Logout'), ['class' => 'btn btn-primary']) ?>
            </div>

            <?php ActiveForm::end(); ?>

    <!-- force logout by company end -->


    

    </div>

</div>

