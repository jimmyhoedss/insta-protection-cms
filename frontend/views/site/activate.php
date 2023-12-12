<?php
use yii\helpers\Html;
use yii\web\View;
use frontend\assets\AntiFraudAsset;

$this->title = Yii::t('Activation', 'Feedback');
$q = http_build_query($_GET);
$url_webcam = Yii::$app->urlManager->createUrl(['site/activate-webcam']);
$url_webcam .= "?" . $q;

?>

<div class="site-activate">
	<!--<a class="snap" href=#>snap</a> <a class="upload" href=#>upload</a>-->
    <div class="container">

	    <div class="instruction">
	        <h1 class="title"><?=Yii::t('frontend','Device Physical Condition Assessment')?></h1>
	        <div><?=Yii::t('frontend','
		    	You will need another mobile device for the QR code assessment of the insured device;')?>
		    		<br><?=Yii::t('frontend','or use <a href="{url}">webcam version</a> (works with modern browsers)', array('url' => $url_webcam )); ?>
		    	</div><hr>

	    	<div><b><?=Yii::t('frontend','Step 1:')?></b> <?=Yii::t('frontend','
	    	Download the Instaprotection App.')?></div>
	    	<div class="screen-holder">
	    		<a target=_blank href="https://apps.apple.com/sg/app/instaprotection/id1497665476"><img class="appstore" src="/img/appstore.png"></a>
                  <a target=_blank href="https://play.google.com/store/apps/details?id=com.ioiolab.instaprotect_droid"><img class="googleplay" src="/img/googleplay.png"></a>
              </div>
              <br>


	    	<div><b><?=Yii::t('frontend','Step 2:')?></b> <?=Yii::t('frontend','On the Home screen, top-right "Gear" icon, go to Settings -> Device Assessment.')?></div>
	    	<div class="screen-holder">
	    		<img class="screen" src="/img/screen-home.png"> &nbsp; 
	    		<img class="screen" src="/img/screen-setting.png">
	    	</div><br>


	    	<div><b><?=Yii::t('frontend','Step 3:')?></b> <?=Yii::t('frontend','Position the QR code in view for a close-up photo.')?></div>
			<div class="screen-holder">
	    		<img class="screen" src="/img/screen-zoom2.png"> &nbsp; 
	    	</div><br>


	    	<div><b><?=Yii::t('frontend','Step 4:')?></b> <?=Yii::t('frontend','Position the QR code in view for a zoomed-out photo.')?></div>
			<div class="screen-holder">
	    		<img class="screen" src="/img/screen-zoom1.png"> &nbsp; 
	    	</div><br>


	    	<div><?=Yii::t('frontend','Assessment photos will be automatically uploaded.')?></div>
		</div>

    </div>
</div>

<?php

    $apiUrl = Yii::$app->urlManagerApi->createAbsoluteUrl(['sys/device-assessment']);
	//$apiUrl = "https://api.instaprotection.site/v1/sys/device-assessment";
	//$apiUrl = "https://api.instaprotection.com/v1/sys/device-assessment";
	//$apiUrl = "http://api.ip.localhost/v1/sys";

    $script = <<<JS
        var apiUrl = '{$apiUrl}';

JS;


    $this->registerJs($script, View::POS_HEAD);
    
    //$js = Url::base() . "/js/tagpath-brand-campaign.js";
    //$this->registerJsFile($js, ['position'=>View::POS_BEGIN]);


?>