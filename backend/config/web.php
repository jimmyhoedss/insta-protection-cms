<?php
$config = [
    //'homeUrl' => Yii::getAlias('@backendUrl'),
    'homeUrl' => 'dashboard/index',
    'controllerNamespace' => 'backend\controllers',
    'defaultRoute' => 'dashboard/index',
    //'defaultRoute' => 'sign-in/login',
    'components' => [
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'request' => [
            'cookieValidationKey' => env('BACKEND_COOKIE_VALIDATION_KEY'),
            'baseUrl' => env('BACKEND_BASE_URL'),
        ],
        'user' => [
            'class' => yii\web\User::class,
            'identityClass' => common\models\User::class,
            'loginUrl' => ['site/login'],
            'enableAutoLogin' => true,
            // 'absoluteAuthTimeout' => 4*60*60, //hard timeout - 4hrs
            // 'authTimeout' => YII_ENV_DEV ? 4*60*60 : 15*60, //idle timeout - 15 mins
            // 'authTimeout' => 15*60, //idle timeout - 15 mins
            'as afterLogin' => common\behaviors\LoginTimestampBehavior::class,
        ],
    ],
    'modules' => [
        'system' => [
            'class' => backend\modules\system\Module::class,
        ],
        'gridview' => [
            'class' => 'kartik\grid\Module',
        ],        
        /*
        'translation' => [
            'class' => backend\modules\translation\Module::class,
        ],
        'select2' => [
            'class' => 'kartik\select2\Select2',
        ]
        */
    ],
    
];

if (YII_ENV_DEV) {
    $config['modules']['gii'] = [
        'class' => yii\gii\Module::class,
        'generators' => [
            'crud' => [
                'class' => yii\gii\generators\crud\Generator::class,
                'templates' => [
                    'yii2-starter-kit' => Yii::getAlias('@backend/views/_gii/templates'),
                ],
                'template' => 'yii2-starter-kit',
                'messageCategory' => 'backend',
            ],
        ],
    ];
}

return $config;
