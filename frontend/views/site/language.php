
<?php    
    use yii\bootstrap\Nav;
    use yii\bootstrap\Dropdown;
    use yii\bootstrap\NavBar;
    use yii\helpers\Url;
    use yii\helpers\ArrayHelper;
    use common\widgets\LanguageSelectorWidget;
    use common\components\LanguageDropdown;
    use yii\base\Widget;
    use yii\helpers\Html;
    
//langauge code : 
//https://www.science.co.il/language/Locale-codes.php
//http://www.lingoes.net/en/translator/langcode.htm  
    $l = Yii::$app->params['availableLocales'];
    // print_r($l);exit();
    $url0 = Url::to(["/site/index",'language' =>$l[0]]);
    $url1 = Url::to(["/site/index",'language' =>$l[1]]);
    $url2 = Url::to(["/site/index",'language' =>$l[2]]);
    $url3 = Url::to(["/site/index",'language' =>$l[3]]);
    $url4 = Url::to(["/site/index",'language' =>$l[4]]);



?>



<div class="site-language" style="display: flex; justify-content: center;">
        <div class="language-holder">
            <h3 class="menu-title ">Select your language</h3>
            <br><br>
            <div><a href="<?php echo $url0; ?>">English</a><hr></div>
            <div><a href="<?php echo $url3; ?>">简体中文</a><hr></div>
            <div><a href="<?php echo $url1; ?>">Thai</a><hr></div>
            <!-- <div><a href="<?php echo $url2; ?>">Viet</a><hr></div> -->
            <!-- <div><a href="<?php echo $url4; ?>">Indonesia</a><hr></div> -->
            
        </div>
</div>