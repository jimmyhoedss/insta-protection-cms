<?php
/**
 * @var $this yii\web\View
 * @var $content string
 */

use backend\assets\BackendAsset;
use backend\modules\system\models\SystemLog;
use backend\widgets\Menu;
use common\models\TimelineEvent;
use common\models\InstapPlanPool;
use yii\bootstrap\Alert;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\log\Logger;
use yii\widgets\Breadcrumbs;
use common\models\User;


if (Yii::$app->user->isGuest) {
   return Yii::$app->response->redirect(['site/login']);
}
$bundle = BackendAsset::register($this);



?>

<?php $this->beginContent('@backend/views/layouts/base.php'); ?>

<div class="wrapper">
    <!-- header logo: style can be found in header.less -->
    <header class="main-header">
        <a href="<?php echo Yii::$app->urlManagerBackend->createAbsoluteUrl('/') ?>" class="logo">
            <!-- Add the class icon to your logo image or logo icon to add the margining -->
            <?php echo Yii::$app->name ?> CMS
        </a>
        <!-- Header Navbar: style can be found in header.less -->
        <nav class="navbar navbar-static-top" role="navigation">
            <!-- Sidebar toggle button-->
            <a href="#" class="sidebar-toggle " data-toggle="push-menu" role="button">
                <span class="sr-only"><?php echo Yii::t('backend', 'Toggle navigation') ?></span>
            </a>
            <div class="navbar-custom-menu">
                <ul class="nav navbar-nav">
                    <!-- force logout all -->

                    <!-- Change country -->
                    <li id="log-dropdown" class="dropdown notifications-menu">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <i class="fa fa-globe-asia"></i>
                            <span class="label label-info">
                                <?php echo Yii::$app->session->get('region_id') ?>
                            </span>
                        </a>
                        <ul class="dropdown-menu">
                            <li class="header"><b><?php echo Yii::t('backend', 'Change Country') ?></b></li>
                            <li>
                                <!-- inner menu: contains the actual data -->
                                <ul class="menu">
                                    <?php foreach (Yii::$app->user->identity->grantedCountryAccessPermissions as $country): ?>
                                        <li>
                                            <?php 
                                                echo Html::a(Yii::$app->user->identity::countryAccessPermissions()[$country], ArrayHelper::merge(['/site/country', 'choice'=>$country], $_GET), ['class' => Yii::$app->session->get('region_id') == strtoupper(substr($country,-2)) ? 'btn btn-info' : 'btn'])
                                            ?>
                                            <!-- <?php echo Html::a(Yii::$app->user->identity::countryAccessPermissions()[$country], ['/site/country', 'choice'=>$country, 'controller'=>Yii::$app->controller->id, 'action'=>Yii::$app->controller->action->id], ['class' => Yii::$app->session->get('region_id') == strtoupper(substr($country,-2)) ? 'btn btn-info' : 'btn']) ?> -->
                                            <!-- <?php echo Html::a(Yii::$app->user->identity::countryAccessPermissions()[$country], ['/site/country', 'choice'=>$country]) ?> -->
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    <!-- Timeline -->
                    <!--
                    <li id="timeline-notifications" class="notifications-menu">
                        <a href="<?php echo Url::to(['/dashboard/index']) ?>">
                            <i class="fa fa-bell"></i>
                            <span class="label label-success">
                                <?php echo TimelineEvent::find()->today()->count() ?>
                            </span>
                        </a>
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
                                        <?php echo Yii::t('backend', 'Member since {0, date, short}', Yii::$app->user->identity->created_at) ?>
                                    </small>
                            </li>
                            <!-- Menu Footer-->
                            <li class="user-footer">
                                <div style=" display:flex; justify-content: space-between; align-items: center;">
                                    <?php echo Html::a(Yii::t('backend', 'Account'), ['user/update-account'], ['class' => 'btn btn-default btn-flat', 'data-method' => 'post']) ?>
                                    <?php echo Html::a(Yii::t('backend', 'Logout'), ['/logout'], ['class' => 'btn btn-default btn-flat', 'data-method' => 'post']) ?>
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
                    <p><?php echo Yii::t('backend', 'Hello, {username}', ['username' => Yii::$app->user->identity->getPublicIdentity()]) ?></p>
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
                        'label' => Yii::t('backend', 'GENERAL'),
                        'options' => ['class' => 'header'],
                    ],
                    /*
                    [
                        'label' => Yii::t('backend', 'Users'),
                        'icon' => '<i class="fa fa-users"></i>',
                        'url' => ['/user/index'],
                        'active' => Yii::$app->controller->id === 'user',
                        'visible' => Yii::$app->user->can('administrator'),
                    ],
                    */
                    [
                        'label' => Yii::t('backend', 'Dashboard'),
                        'icon' => '<i class="fa fa-home"></i>',
                        'url' => ['/dashboard/index'],
                        'badge' => TimelineEvent::find()->today()->count() > 0 ? TimelineEvent::find()->today()->count() : "",
                        'badgeBgClass' => 'label-success',
                    ],
                    [
                        'label' => Yii::t('backend', 'Statistics'),
                        'url' => ['/dashboard/statistics'],
                        // 'active' => Yii::$app->controller->id == 'dashboard',
                        'icon' => '<i class="fa fa-chart-line"></i>',
                    ],
                    [
                        'label' => Yii::t('backend', 'All Users'),
                        'url' => ['/user/index'],
                        'active' => Yii::$app->controller->id == 'user',
                        'icon' => '<i class="fa fa-users"></i>',
                    ],
                    /*[
                        'label' => Yii::t('backend', 'Report Generator'),
                        'url' => ['/report/index'],
                        'active' => Yii::$app->controller->id == 'report',
                        'icon' => '<i class="fa fa-file-export"></i>',
                    ],*/
                    [
                        'label' => Yii::t('backend', 'CUSTOMER'),
                        'options' => ['class' => 'header'],
                    ],                 
                    [
                        'label'=>Yii::t('backend', 'Retail'),
                        'url' => '#',
                        'icon'=>'<i class="fa fa-cash-register"></i>',
                        'options'=>['class'=>'treeview'],
                        'items'=>[
                            ['label'=>'Plan Offerings', 'url'=>['/instap-plan/index'], 'icon'=>'<i class="fa fa-angle-double-right"></i>', 
                                'active' => Yii::$app->controller->id == 'instap-plan'],
                            ['label'=>'Promotional Banners', 'url'=>['instap-banner/index'], 'icon'=>'<i class="fa fa-angle-double-right"></i>',
                            'active' => Yii::$app->controller->id == 'instap-banner'],
                        ]
                    ],                        
                    [
                        'label'=>Yii::t('backend', 'Policies'),
                        'url' => '#',
                        'icon'=>'<i class="fa fa-clipboard-list"></i>',
                        'options'=>['class'=>'treeview'],
                        'items'=>[
                            [
                                'label'=>'Activations', 'url'=>['/instap-plan-pool/index'], 
                                'icon'=>'<i class="fa fa-angle-double-right"></i>', 
                                'active' => Yii::$app->controller->id == 'instap-plan-pool' | Yii::$app->controller->id == 'user-plan-detail'
                            ],
                            /*
                            [
                                'label'=>'Active', 'url'=>['/instap-plan-pool/index', 
                                    'InstapPlanPoolSearch[plan_status]'=>InstapPlanPool::STATUS_ACTIVE], 
                                'icon'=>'<i class="fa fa-angle-double-right"></i>', 
                                'active' => Yii::$app->controller->id == 'instap-plan-pool' && 
                                    Yii::$app->getRequest()->getQueryParam('InstapPlanPoolSearch')['plan_status'] == InstapPlanPool::STATUS_ACTIVE
                            ],
                            */
                            [
                                'label'=>'Claims', 'url'=>['/user-case/index'], 'icon'=>'<i class="fa fa-angle-double-right"></i>',
                                'active' => Yii::$app->controller->id == 'user-case'
                            ],

                        ]
                    ],
                    /*[
                        'label'=>Yii::t('backend', 'Reports'),
                        'url' => '#',
                        'icon'=>'<i class="fa fa-file-download"></i>',
                        'options'=>['class'=>'treeview'],
                        'items'=>[
                            [
                                'label'=>'Generate Report', 'url'=>['/instap-report/report-history'], 'icon'=>'<i class="fa fa-angle-double-right"></i>',
                                'active' => Yii::$app->controller->id == 'instap-report'
                            ],

                        ]
                    ],*/      
                    [
                        'label' => Yii::t('backend', 'Reports'),
                        'url' => ['/instap-report/declaration-report'],
                        'icon' => '<i class="fa fa-file-download"></i>',
                        'active' => Yii::$app->controller->id == 'instap-report',
                    ],            
                    [
                        'label' => Yii::t('backend', 'ENTERPRISE'),
                        'options' => ['class' => 'header'],
                    ],
                    [
                        'label' => Yii::t('backend', 'Dealer'),
                        'url' => '#',
                        'icon'=>'<i class="fa fa-store"></i>',
                        'options'=>['class'=>'treeview'],
                        'items'=>[
                            ['label'=>'Companies', 'url'=>['/dealer-company/index'], 'icon'=>'<i class="fa fa-angle-double-right"></i>', 'active' => Yii::$app->controller->id == 'dealer-company'],
                            ['label'=>'Staffs', 'url'=>['/dealer-user/index'], 'icon'=>'<i class="fa fa-angle-double-right"></i>', 'active' => strpos(Yii::$app->request->url, 'dealer-user/') !== false],
                            ['label'=>'Staff Movement Logs', 'url'=>['/dealer-user-history/index'], 'icon'=>'<i class="fa fa-angle-double-right"></i>', 'active' => strpos(Yii::$app->request->url, 'dealer-user-history/') !== false],
                            ['label'=>'Company Relationships', 'url'=>['/dealer-company-dealer/index'], 'icon'=>'<i class="fa fa-angle-double-right"></i>', 'active' => strpos(Yii::$app->request->url, 'dealer-company-dealer/') !== false, 'visible' =>Yii::$app->authManager->checkAccess(Yii::$app->user->id, User::ROLE_IP_ADMINISTRATOR) || Yii::$app->authManager->checkAccess(Yii::$app->user->id, User::ROLE_IP_MANAGER)],

                        ]
                    ],
                    /*[
                        'label' => Yii::t('backend', 'Dealer Order'),
                        'url' => ['/dealer-order/index'],
                        'icon' => '<i class="fa fa-cart-plus"></i>',
                    ],*/
                    [
                        'label' => Yii::t('backend', 'Inventories'),
                        'url' => ['/dealer-order-inventory-overview/index'],
                        'icon' => '<i class="fa fa-warehouse"></i>',
                        'active' => Yii::$app->controller->id == 'dealer-order-inventory-overview',
                        'visible' =>Yii::$app->authManager->checkAccess(Yii::$app->user->id, User::ROLE_IP_ADMINISTRATOR) || Yii::$app->authManager->checkAccess(Yii::$app->user->id, User::ROLE_IP_MANAGER)
                    ],
                    [
                        'label' => Yii::t('backend', 'Repair Centres'),
                        'url' => ['/qcd-repair-centre/index'],
                        'active' => Yii::$app->controller->id == 'qcd-repair-centre' || Yii::$app->controller->id == 'permission',
                        'icon' => '<i class="fa fa-hammer"></i>',
                        'visible' =>Yii::$app->authManager->checkAccess(Yii::$app->user->id, User::ROLE_IP_ADMINISTRATOR) || Yii::$app->authManager->checkAccess(Yii::$app->user->id, User::ROLE_ADMINISTRATOR)
                    ],
                    [
                        'label' => Yii::t('backend', 'SYSTEM'),
                        'options' => ['class' => 'header'],
                    ],
                    [
                        'label' => Yii::t('backend', 'System'),
                        'url' => '#',
                        'icon'=>'<i class="fa fa-cogs"></i>',
                        'options'=>['class'=>'treeview'],
                        'items'=>[
                            ['label'=>'Settings', 'url'=>['/system/settings'], 'icon'=>'<i class="fa fa-angle-double-right"></i>', 'visible' => Yii::$app->authManager->checkAccess(Yii::$app->user->id, User::ROLE_ADMINISTRATOR)],
                            // ['label'=>'Key Storage', 'url'=>['/system/key-storage'], 'icon'=>'<i class="fa fa-angle-double-right"></i>'],
                            ['label'=>'Tools', 'url'=>['/sys-settings/tools'], 'icon'=>'<i class="fa fa-angle-double-right"></i>','visible' =>Yii::$app->authManager->checkAccess(Yii::$app->user->id, User::ROLE_IP_ADMINISTRATOR) || Yii::$app->authManager->checkAccess(Yii::$app->user->id, User::ROLE_ADMINISTRATOR)],
                            ['label'=>'Cache', 'url'=>['/system/cache'], 'icon'=>'<i class="fa fa-angle-double-right"></i>', 'visible' => Yii::$app->authManager->checkAccess(Yii::$app->user->id, User::ROLE_ADMINISTRATOR)],
                            ['label'=>'Activity Logs', 'url'=>['/activity-log/audit-trail-log'], 'icon'=>'<i class="fa fa-angle-double-right"></i>', 'visible' => Yii::$app->authManager->checkAccess(Yii::$app->user->id, User::ROLE_ADMINISTRATOR)],

                        ]
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
