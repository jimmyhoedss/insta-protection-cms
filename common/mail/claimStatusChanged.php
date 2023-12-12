<?php
use yii\helpers\Html;
use common\models\fcm\FcmCaseStatusChanged;

$body = FcmCaseStatusChanged::summary()[$current_case_status]."\n".$policy_number;
?>
<br><?=Yii::t('email','Hi Sir/ Madam,')?> 
<br><br><?= $body ?>
<br><br><?=Yii::t('email','Please kindly log in to the application/website to check.')?>
<br><br>
<?=Yii::t('email','Contact our Customer Service should you need any assistance.')?>
<br><br>
<br>Best Regards, 
<br>InstaProtection
