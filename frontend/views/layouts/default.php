<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Url;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use frontend\assets\AppAsset;
use common\widgets\Alert;
use yii\web\View;
use yii\helpers\ArrayHelper;


AppAsset::register($this);
$this->render('_meta-tag');
$ga = Yii::$app->params["ga_trackingId"];

?>

<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>

    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-127667245-2"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      gtag('config', '<?php echo $ga; ?>');
    </script>


    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <link rel="shortcut icon" id="ip-favicon" type="image/png" href="/favicon.png"/>
    <?php
    $route = Yii::$app->getRequest()->getPathInfo();
    ?>

    <?php $this->head() ?>
</head>
<body>


<?php $this->beginBody() ?>

<div class="wrap">

  <?php 
    Yii::$app->view->params['layout'] = 'landing';
    $this->beginContent('@app/views/layouts/_top-nav.php');
    $this->endContent(); 
  ?>

  <div class="main-navbar-spacer"></div>

  <div class="sub-wrapper">

        <?php if(Yii::$app->session->hasFlash('alert')): ?>
            <div class="alert-container container-sm">
            <?php echo \yii\bootstrap\Alert::widget([
                'body'=>ArrayHelper::getValue(Yii::$app->session->getFlash('alert'), 'body'),
                'options'=>ArrayHelper::getValue(Yii::$app->session->getFlash('alert'), 'options'),
            ])?>
            </div>
        <?php endif; ?>


      <?php 
        echo $content;
      ?>



      
    
  </div>


</div>


      <?php 
        $this->beginContent('@app/views/layouts/_footer.php');
        $this->endContent(); 
      ?>
<?php $this->endBody() ?>
<?php
    $js = Url::base() . "/js/app.js";
    $this->registerJsFile($js, ['position'=>View::POS_END]);
?>
</body>
</html>
<?php $this->endPage() ?>


