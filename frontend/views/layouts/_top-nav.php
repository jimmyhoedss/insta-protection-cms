<?php    
    use yii\bootstrap\Nav;
    use yii\bootstrap\Dropdown;
    use yii\bootstrap\NavBar;
    use yii\helpers\Url;
    use yii\helpers\ArrayHelper;
    use common\widgets\LanguageSelectorWidget;
    use yii\base\Widget;
    use yii\helpers\Html;
    use common\components\Utility;


?>



    <nav class="main-navbar main-navbar-fixed">
        <div class="navbar-container">
            <div class="logo-holder">  
                <a href="<?php  echo Url::to(['/']);  ?>"><h1 class="logo-ip text-hide">InstaProtection</h1></a>
            </div>
           

            <ul class="menu-link-holder hidden-sm hidden-xs">
                <li class="nav-item first-item">
                    <a class="no-underline" href="<?php echo Url::to(['/app']); ?>"><?php echo Yii::t('frontend', 'App'); ?></a>
                </li>
                <li class="nav-item first-item">
                    <a class="no-underline" href="<?php echo Url::to(['/terms']); ?>"><?php echo Yii::t('frontend', 'Terms'); ?></a>
                </li>
                <li class="nav-item first-item">
                    <a class="no-underline" href="<?php echo Url::to(["/site/activate"]); ?>"><?php echo Yii::t('frontend', 'Self-assessment Instructions'); ?></a>
                </li> 
              <!--  <li class="nav-item first-item">
                    <a class="no-underline" href="<?php echo Url::to(["/user/check-imei"]); ?>"><?php echo Yii::t('frontend', 'IMEI'); ?></a>
                </li>    -->
          
            </ul>

            <ul class="menu-right-holder pull-right">
                <li class="nav-item  hidden-sm hidden-xs">
                    <a class="no-underline" href="<?= Yii::$app->urlManagerDashboard->createAbsoluteUrl('/') ?>"><?php echo Yii::t('frontend', 'Customer / Claim Access'); ?></a>
                </li>
                <!--  <li class="nav-item  hidden-sm hidden-xs">
                    <a class="no-underline" href="<?php echo Url::to(["/site/language"]); ?>"><?php echo Yii::t('frontend', 'Language'); ?></a>
                </li>  -->
                <li class="compact-menu nav-item visible-sm-inline-block visible-xs-inline-block">
                  <i class="fa fa-bars"></i>  
                </li>
            </ul>


        </div>
        
    </nav>


    <nav id="fullscreen-nav">
        <span class="close">×</span>
        <div class="logo-holder">  
                <a href="/"><h1 class="logo-ip text-hide">InstaProtection</h1></a>
        </div>
        <h3 class="menu-title menu">Menu</h3>
        <ul class="">
            <li class="nav-item">
                <a class="no-underline" href="<?php echo Url::to(['/app']); ?>"><?php echo Yii::t('frontend', 'App'); ?></a>
            </li>            
            <li class="nav-item">
                <a class="no-underline" href="<?php echo Url::to(['/terms']); ?>"><?php echo Yii::t('frontend', 'Terms'); ?></a>
            </li>
            <li class="nav-item">
                <a class="no-underline" href="<?php echo Url::to(["/site/activate"]); ?>"><?php echo Yii::t('frontend', 'Self-assessment Instructions'); ?></a>
            </li>
            <br/>
            <br/>
            <li>
                <a class="no-underline" href="<?= Yii::$app->urlManagerDashboard->createAbsoluteUrl('/') ?>"><?php echo Yii::t('frontend', 'Customer / Claim Access'); ?></a>
            </li>
          <!--   <li>
                <a class="no-underline" href="<?php echo Url::to(["/site/language"]); ?>"><?php echo Yii::t('frontend', 'Language'); ?></a>
            </li> -->
            
        </ul>

        <br><br><br>
    </nav>

<!-- <script type="text/javascript">
    alert("Language ->>>>>>>>>>>>>>>",navigator.languages);
</script> -->