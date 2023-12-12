<?php
use yii\helpers\Html;


$this->title = Yii::t('frontend', 'Feedback');
if (!isset($msg)) $msg = "";

?>


<div class="site-feedback">

    <div class="container container-sm">
        <div class="overview">
            <br><br><br><br><br><br>
            <h3 class="menu-title menu-title-tight text-center">THANK YOU</h3>
            <p class="text-center"><?php echo \yii\helpers\HtmlPurifier::process($msg); ?></p>

        </div>
    </div>
</div>