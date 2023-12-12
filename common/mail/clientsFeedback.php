<?php 
	use yii\helpers\Html;
?>

Feedback Received,
<br>
<br>You've received a <?php echo Html::encode($subject); ?> feedback from a user.
<hr>name : <?php echo Html::encode($name); ?>
<br>email : <?php echo Html::encode($user); ?>
<br>message : <?php echo Html::encode($message); ?><br>
<br>Regards,
<br>NParks Programming & Events Branch
