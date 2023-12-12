<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\log\Logger;
use backend\modules\system\models\SystemLog;
use common\models\User;

$this->title = Yii::t('backend', 'System Tools');



?>

<div class="box">
    <div class="box-body">
        <ul>
            <?php if(Yii::$app->authManager->checkAccess(Yii::$app->user->id, User::ROLE_ADMINISTRATOR)){ ?>
            <li id="log-dropdown">
                <?php $url = Url::to(['/sys-settings/force-logout']); ?>
                <a href="<?=$url?>" class="dropdown-toggle">
                    <i class="fa fa-sign-out-alt"></i>
                    &nbsp;&nbsp;Force logout
                </a>
            </li>
            <br>
            <!-- Notifications: style can be found in dropdown.less -->
            <li id="log-dropdown">
                <?php $url = Url::to(['/system/log/index']); ?>
                <a href="<?=$url?>" class="dropdown-toggle">
                    <i class="fa fa-warning"></i>
                    <span class="label label-danger">
                        <?php echo SystemLog::find()->count() ?> 
                    </span>
                    &nbsp;&nbsp;System logs
                </a>
                
            </li>

            <br>
            <li id="log-dropdown">
                <?php $url = Url::to(['/reset-db/reset-db']); ?>
                <a href="<?=$url?>" class="dropdown-toggle" onclick="return confirm('Reset to default DB?')">
                    <i class="fa fa-database"></i>
                    &nbsp;&nbsp;Reset Database (DB)
                </a>
                
            </li>
             <br>
            <?php } ?>
            <li id="log-dropdown">
                <?php $url = Url::to(['/sys-settings/otp-list']); ?>
                <a href="<?=$url?>" class="dropdown-toggle">
                    <i class="fa fa-list-alt"></i>
                    &nbsp;&nbsp;One-Time Pin (OTP) list
                </a>
                
            </li>
                


        </ul>
    </div>
</div>

