<?php
use backend\assets\BackendAsset;
use common\assets\FontAwesome;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;

/* @var $this \yii\web\View */
/* @var $content string */

$bundle = BackendAsset::register($this);
FontAwesome::register($this);

$this->params['body-class'] = $this->params['body-class'] ?? null;
?>

<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?php echo Yii::$app->language ?>">
<head>
    <meta charset="<?php echo Yii::$app->charset ?>">
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>

    <?php echo Html::csrfMetaTags() ?>
    <title><?php echo Html::encode($this->title) ?></title>
    <?php $this->head() ?>

</head>
<?php echo Html::beginTag('body', [
    'class' => implode(' ', [
        ArrayHelper::getValue($this->params, 'body-class'),
        'skin-black', 'fixed', null, null, null,
    ])
])?>
    <?php $this->beginBody() ?>
        <?php echo $content ?>
    <?php $this->endBody() ?>
<?php echo Html::endTag('body') ?>
</html>
<?php $this->endPage() ?>
