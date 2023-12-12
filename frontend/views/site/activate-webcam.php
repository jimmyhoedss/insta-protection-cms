<?php
use yii\helpers\Html;
use yii\web\View;
use frontend\assets\AntiFraudAsset;

$this->title = Yii::t('Activation', 'Feedback');

AntiFraudAsset::register($this);

?>

<div class="site-activate">
	<!--<a class="snap" href=#>snap</a> <a class="upload" href=#>upload</a>-->
    <div class="container">
        <h1 class="title"><?=Yii::t('frontend','Device Physical Condition Assessment')?></h1>
	    <div class="instruction">
	    	<div><b><?=Yii::t('frontend','Step 1:')?></b> <?=Yii::t('frontend','Go to your care plan page and press "Start Assessment".')?></div>
	    	<div><b><?=Yii::t('frontend','Step 2:')?></b> <?=Yii::t('frontend','Position the QR code in view for a zoom out photo.')?></div>
	    	<div><b><?=Yii::t('frontend','Step 3:')?></b> <?=Yii::t('frontend','Position the QR code in view for a close up photo.')?></div>
	    	<div><b><?=Yii::t('frontend','Step 4:')?></b> <?=Yii::t('frontend','Assessment photos will be automatically uploaded.')?></div>
		</div>

	    <!-- <div id="loading-message">Unable to access video stream (please make sure you have a webcam enabled)</div> -->
	    <div class="hint-holder">
		    <canvas id="canvas"></canvas>
			<div class="hint-bg">
				<p id="hint"><?=Yii::t('frontend','Please make sure you have a webcam enabled')?></p>
			</div>
		</div>
	    <!-- <div id="output" hidden>
	      <div id="output-message">No QR code detected.</div>
	      <div hidden><b>Data:</b> <span id="output-data"></span></div>
	    </div> -->
	    <div class="photo-container"></div>
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