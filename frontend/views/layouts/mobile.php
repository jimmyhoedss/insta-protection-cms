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

?>

<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>


    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <link rel="shortcut icon" id="ip-favicon" type="image/png" href="/favicon.png"/>
    <?php
    //$route = Yii::$app->getRequest()->getPathInfo();
    ?>

    <?php $this->head() ?>
    <style>
      .sub-wrapper{padding: 20px;}
    </style>
</head>
<body>


<?php $this->beginBody() ?>

<div class="wrap">

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
        if (class_exists('yii\debug\Module')) {
            $this->off(\yii\web\View::EVENT_END_BODY, [\yii\debug\Module::getInstance(), 'renderToolbar']);
        } 
      ?>
<?php $this->endBody() ?>

</body>
</html>
<?php $this->endPage() ?>


