<?php
use yii\helpers\Html;

?>
<br><?=Yii::t('common','Hi {0},', [$repair_centre])?>
<br><br><br><?=Yii::t('common','InstaProtection has a new service request.')?> 
<br><br><br><?=Yii::t('common','The service request information are as follows:')?> 

<br><br><?=Yii::t('common','1. Claim ID: {0}', [$claim_id])?> 
<br><?=Yii::t('common','2. Customer Name: {0}', [$user_name])?> 
<br><?=Yii::t('common','3. Phone Model: {0}', [$device_model])?> 
<br><?=Yii::t('common','4. Phone IMEI: {0}', [$device_imei])?> 
<br><?=Yii::t('common','5. Phone S/N: {0}', [$device_serial])?> 
<br><?=Yii::t('common','6. Plan Name: {0}', [$plan_name])?> 


<br><br><br><?=Yii::t('common','Pls note that once service request is completed, kindly attached the listed documents and email back to us.')?>


<br><br><?=Yii::t('common','1. SERVICES REPORT without repair cost')?>
<br><?=Yii::t('common','2. QUOTATION')?>
<br><?=Yii::t('common','3. DAMAGE PHOTO')?>
<br><?=Yii::t('common','4. REPAIR DONE PHOTO')?>
<br><br><?=Yii::t('common','Should you have any issues, pls kindly contact customercare@instaprotection.com.')?>

<br><br><br><?=Yii::t('common','Thanks and regards,')?>
<br><?=Yii::t('common','InstaProtection')?>

<!-- //ip logo here
<image011.png> -->