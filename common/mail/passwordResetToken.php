<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user common\models\User */
/* @var $token string */


//Html::encode($user->username);
//$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['/user/password-reset', 'token' => $token]);
$resetLink = Yii::$app->urlManagerFrontend->createAbsoluteUrl(['/user/password-reset', 'token' => $token]);
?>
Reset Your Password
<br>
<br>We have received a request to have your password reset for your account. If you did not make this request, please ignore this email. 
<br><br>Please click on the link below to reset your password:
<br><?php echo Html::a(Html::encode($resetLink), $resetLink) ?>
<br>
<br>Regards,
<br>NParks Programming & Events Branch