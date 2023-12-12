<?php
/**
 * @var $this yii\web\View
 * @var $content string
 */

use dashboard\assets\DashboardAsset;
use dashboard\widgets\Menu;
use common\models\TimelineEvent;
use yii\bootstrap\Alert;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Nav;
use yii\log\Logger;
use yii\widgets\Breadcrumbs;

$bundle = DashboardAsset::register($this);

?>

<?php $this->beginContent('@dashboard/views/layouts/base.php'); ?>

<div class="wrapper">
    <!-- header logo: style can be found in header.less -->
    <header class="main-header">
        <a href="<?php echo Yii::$app->urlManagerDashboard->createAbsoluteUrl('/') ?>" class="logo">
            <!-- Add the class icon to your logo image or logo icon to add the margining -->
            <?php echo Yii::$app->name ?>
        </a>
        <!-- Header Navbar: style can be found in header.less -->
        <nav class="navbar navbar-static-top" role="navigation">
            <!-- Sidebar toggle button-->
            <!--
            <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
                <span class="sr-only"><?php echo Yii::t('backend', 'Toggle navigation') ?></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </a>
            -->
            <div class="navbar-custom-menu">
                <ul class="nav navbar-nav">
                    <!-- 
                    <li id="log-dropdown" class="dropdown notifications-menu">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <i class="fa fa-language"></i>
                            <span class="label label-info">
                                <?php echo strtoupper(substr(Yii::$app->language,0,2))  ?>
                            </span>
                        </a>
                        <ul class="dropdown-menu">
                            <li class="header"><b><?php echo Yii::t('backend', 'Change Language') ?></b></li>
                            <li>
                                <ul class="menu">
                                    <?php foreach (Yii::$app->params['availableLocales'] as $language): ?>
                                        <li>
                                            <?php 
                                                // echo Html::a(Yii::$app->params['languages'][$language], ArrayHelper::merge([Yii::$app->controller->id."/".Yii::$app->controller->action->id, 'language'=>$language], $_GET), ['class' => Yii::$app->language == $language ? 'btn btn-info' : 'btn'])
                                            ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                        </ul> 
                    </li>
                    -->
                    <!-- User Account: style can be found in dropdown.less -->
                    <li class="dropdown user user-menu">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <img src="<?php echo Yii::$app->user->identity->userProfile->getAvatar() ?>"
                                 class="user-image">
                            <span><?php echo Yii::$app->user->identity->userProfile->first_name ?> <i class="caret"></i></span>
                        </a>
                        <ul class="dropdown-menu">
                            <!-- User image -->
                            <li class="user-header light-blue">
                                <img src="<?php echo Yii::$app->user->identity->userProfile->getAvatar() ?>"
                                     class="img-circle" alt="User Image"/>
                                <p>
                                    <?php echo Yii::$app->user->identity->userProfile->first_name ?>
                                    <small>
                                        <?php echo Yii::t('dashboard', 'Member since {0, date, short}', Yii::$app->user->identity->created_at) ?>
                                    </small>
                            </li>
                            <!-- Menu Footer-->
                            <li class="user-footer">
                                <div class="pull-right">
                                    <?php echo Html::a(Yii::t('dashboard', 'Logout'), ['/logout'], ['class' => 'btn btn-default btn-flat', 'data-method' => 'post']) ?>
                                </div>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>
    </header>
    <!-- Left side column. contains the logo and sidebar -->
    <aside class="main-sidebar">
        <!-- sidebar: style can be found in sidebar.less -->
        <section class="sidebar">
            <!-- Sidebar user panel -->
            <div class="user-panel">
                <div class="pull-left image">
                    <img src="<?php echo Yii::$app->user->identity->userProfile->getAvatar() ?>" class="img-circle" />
                </div>
                <div class="pull-left info">
                    <p>
                        <?php echo Yii::t('dashboard', '{username}', ['username' => Yii::$app->user->identity->getPublicIdentity()]) ?>
                    </p>
                    <div class="mobile">
                        <?php echo Yii::$app->user->identity->getFormatMobileNumber(); ?>
                    </div>
                    <a href="<?php echo Url::to(['/sign-in/profile']) ?>">
                        <i class="fa fa-circle text-success"></i>
                        <?php echo Yii::$app->formatter->asDatetime(time()) ?>
                    </a>
                </div>
            </div>
            
            <!-- sidebar menu: : style can be found in sidebar.less -->
            <?php echo Menu::widget([
                'options' => ['class' => 'sidebar-menu tree', 'data' => ['widget' => 'tree']],
                'linkTemplate' => '<a href="{url}">{icon}<span>{label}</span>{right-icon}{badge}</a>',
                'submenuTemplate' => "\n<ul class=\"treeview-menu\">\n{items}\n</ul>\n",
                'activateParents' => true,
                'items' => [
                    [
                        'label' => Yii::t('dashboard', 'General'),
                        'options' => ['class' => 'header'],
                    ],
                    [
                        'label' => Yii::t('dashboard', 'Home'),
                        'icon' => '<i class="fa fa-home"></i>',
                        // 'visible' => Yii::$app->user->can('administrator'),
                        'url' => ['/policy/index'],
                    ],
                ],
            ]) ?>
        </section>
        <!-- /.sidebar -->
    </aside>

    <!-- Right side column. Contains the navbar and content of the page -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                <?php echo $this->title ?>
                <?php if (isset($this->params['subtitle'])): ?>
                    <small><?php echo $this->params['subtitle'] ?></small>
                <?php endif; ?>
            </h1>

            <?php echo Breadcrumbs::widget([
                'tag' => 'ol',
                'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
            ]) ?>
        </section>

        <!-- Main content -->
        <section class="content">
            <?php if (Yii::$app->session->hasFlash('alert')): ?>
                <?php echo Alert::widget([
                    'body' => ArrayHelper::getValue(Yii::$app->session->getFlash('alert'), 'body'),
                    'options' => ArrayHelper::getValue(Yii::$app->session->getFlash('alert'), 'options'),
                ]) ?>
            <?php endif; ?>

            <?php if (Yii::$app->session->hasFlash('success')): ?>
                <div class="alert alert-success alert-dismissable">
                     <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                     <h4><i class="icon fa fa-check"></i>Success!</h4>
                     <?= Yii::$app->session->getFlash('success') ?>
                </div>
            <?php endif; ?>

            <?php if (Yii::$app->session->hasFlash('error')): ?>
                <div class="alert alert-danger alert-dismissable">
                     <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                     <h4><i class="icon fa fa-check"></i>Error!</h4>
                     <?= Yii::$app->session->getFlash('error') ?>
                </div>
            <?php endif; ?>
            <?php echo $content ?>
        </section><!-- /.content -->
    </div><!-- /.right-side -->

    <footer class="main-footer">
        <strong>&copy; <?php echo date('Y') ?> InstaProtection. All rights reserved. </strong>
    </footer>
</div><!-- ./wrapper -->

<?php $this->endContent(); ?>
