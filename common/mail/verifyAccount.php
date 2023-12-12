<?php
use yii\helpers\Html;

//$verifyLink = Yii::$app->urlManager->createAbsoluteUrl(['/user/verify', 'token' => $token]);
$verifyLink = Yii::$app->urlManagerFrontend->createAbsoluteUrl(['/user/verify', 'token' => $token]);
// $verifyLink = https://instaprotection.site/user/verify?token=123
// $verifyLink = "http://ip.localhost/site/verify?token=".$token;

?>
<br>Verify Your Email Address 
<br>
<br>Thank you for creating an account with <?php echo Html::encode(Yii::$app->name); ?>!
<br>Please click the link below to verify your email address:
<br><?php echo Html::a(Html::encode($verifyLink), $verifyLink); ?>
<br>
<br>Regards,
<br>Instaprotection 


