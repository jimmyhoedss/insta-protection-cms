<?php
$config = [
    //'homeUrl' => Yii::getAlias('@backendUrl'),
    'homeUrl' => '/',
    'controllerNamespace' => 'dashboard\controllers',
    'defaultRoute' => 'policy/index',
    //'defaultRoute' => 'sign-in/login',
    'components' => [
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'request' => [
            'cookieValidationKey' => env('DASHBOARD_COOKIE_VALIDATION_KEY'),
            'baseUrl' => env('DASHBOARD_BASE_URL'),
        ],
        'user' => [
            'class' => yii\web\User::class,
            'identityClass' => common\models\User::class,
            'loginUrl' => ['site/login'],
            'enableAutoLogin' => true,
            'as afterLogin' => common\behaviors\LoginTimestampBehavior::class,
        ],
        'maintenance' => [
            'class' => common\components\maintenance\Maintenance::class,
            'enabled' => function ($app) {
                if (env('APP_MAINTENANCE') === '1') {
                    return true;
                }
                return $app->keyStorage->get(common\models\KeyStorageItem::DASHBOARD_MAINTENANCE) === 'enabled';
            }
        ],
    ],
    'modules' => [
        'system' => [
            'class' => dashboard\modules\system\Module::class,
        ],
        'translation' => [
            'class' => dashboard\modules\translation\Module::class,
        ],
        'gridview' => [
            'class' => 'kartik\grid\Module',
        ],
        'select2' => [
            'class' => 'kartik\select2\Select2',
        ]
    ],
    
];

if (YII_ENV_DEV) {
    $config['modules']['gii'] = [
        'class' => yii\gii\Module::class,
        'generators' => [
            'crud' => [
                'class' => yii\gii\generators\crud\Generator::class,
                'templates' => [
                    'yii2-starter-kit' => Yii::getAlias('@dashboard/views/_gii/templates'),
                ],
                'template' => 'yii2-starter-kit',
                'messageCategory' => 'dashboard',
            ],
        ],
    ];
}

return $config;
