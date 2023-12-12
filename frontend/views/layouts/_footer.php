<?php


use yii\helpers\Url;
use common\models\SysRegion;

$lang = Yii::$app->language;
$region_id = "";
$region_id = SysRegion::mapLanguageToCountry($lang);
$WhatsAppLink = SysRegion::getWhatsappBusinessUrl($region_id);
// print_r($region_id);exit();
$WhatsAppNumber = SysRegion::getContactNumber()[$region_id];
// $WhatsAppNumber = "421343";

?>
<div class="footer-section">

    <div class="links container">
            

        <div class="align-bottom">
            <div class="row inline-block">
                <div class="icon"><i class="fa fa-whatsapp"></i></div>
                <div class="text"><a class="no-underline" target="_blank" href="<?=$WhatsAppLink?>">Chat in WhatsApp</a></div>
            </div>            
            <div class="row">
                <div class="icon"><i class="fa fa-phone"></i></div>
                <div class="text"><?=$WhatsAppNumber?></div>
            </div>
            <!-- <div class="row">
                <div class="icon"><i class="fa fa-envelope"></i></div>
                <div class="text">
                    <a href="mailto:ching@instaprotection.com">ching@instaprotection.com</a> &nbsp; 
                    <a href="mailto:freddie@instaprotection.com">freddie@instaprotection.com</a>
                </div>
            </div> -->
            <div class="row">
                <div class="icon"><i class="fa fa-globe"></i></div>
                <div class="text">7500A Beach Road The Plaza #05-319, Singapore 199591</div>
            </div>
        </div>
                    
    </div>
    <div class="footer">
        <div class="container">
            <div class="copyright">&copy; <?php echo date('Y') ?> InstaProtection. All rights reserved. &nbsp;
                <span class="nobr"><a class="white" href="<?php echo Url::to(['/terms']); ?>">Term of Use.</a> &nbsp;<a class="white" href="<?php echo Url::to(['/privacy']); ?>">Privacy Policy.</a></span>
            </div>
        </div>
    </div>
</div>
