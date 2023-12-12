<?php
$config = [
    'name' => 'InstaProtection',
    'vendorPath' => __DIR__ . '/../../vendor',
    'extensions' => require(__DIR__ . '/../../vendor/yiisoft/extensions.php'),
    'bootstrap' => ['log', 'queue', 'rabbitMq'],
    'language' => "en", //set default language
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
        '@almasaeed2010' => '@vendor/almasaeed2010',
    ],
    'components' => [
        'authManager' => [
            'class' => yii\rbac\DbManager::class,
            'itemTable' => '{{%rbac_auth_item}}',
            'itemChildTable' => '{{%rbac_auth_item_child}}',
            'assignmentTable' => '{{%rbac_auth_assignment}}',
            'ruleTable' => '{{%rbac_auth_rule}}'
        ],
        
        'cache' => [
            'class' => yii\caching\FileCache::class,
            'cachePath' => '@common/runtime/cache'
        ],

        'fcm' => [
            'class' => 'understeam\fcm\Client', 'apiKey' => env('FCM_SERVER_KEY') 
        ],

        'commandBus' => [
            'class' => trntv\bus\CommandBus::class,
            'middlewares' => [
                [
                    'class' => trntv\bus\middlewares\BackgroundCommandMiddleware::class,
                    'backgroundHandlerPath' => '@console/yii',
                    'backgroundHandlerRoute' => 'command-bus/handle',
                ]
            ]
        ],

        'formatter'=>[
            'class' => yii\i18n\Formatter::class,
            'defaultTimeZone' => 'UTC',
            'timeZone' => 'Asia/Singapore',            
            'dateFormat' => 'php:d M Y',
            'datetimeFormat' => 'php:d M Y h:i:s A',
            'thousandSeparator' => ',',
            'decimalSeparator' => '.',
            //'currencyCode' => 'USD',
            /*
            'numberFormatterSymbols' => [
                NumberFormatter::CURRENCY_SYMBOL => '$',
                ],
            'numberFormatterOptions' =>
                [
                NumberFormatter::MIN_FRACTION_DIGITS => 0,
                NumberFormatter::MAX_FRACTION_DIGITS => 0,
                ],
            */

        ],

        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
            'useFileTransport' => false,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                
                'host' =>  env('SMTP_HOST'),
                'username' => env('SMTP_USERNAME'),
                'password' => env('SMTP_PASSWORD'),
                'port' => env('SMTP_PORT'),
                'encryption' => 'tls',
                
                'streamOptions' => [ //for localhost testing
                    'ssl' => [
                        'verify_peer' => false,
                        'allow_self_signed' => true
                    ],
                ],
                
            ],
            
            'messageConfig' => [
                'charset' => 'UTF-8',
                'from' => env('ROBOT_EMAIL')
            ]
            
            
        ],

        'db' => [
            'class' => yii\db\Connection::class,
            'dsn' => env('DB_DSN'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'tablePrefix' => env('DB_TABLE_PREFIX'),
            'charset' => env('DB_CHARSET', 'utf8'),
            'enableSchemaCache' => YII_ENV_PROD,
        ],

        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                'db' => [
                    'class' => 'yii\log\DbTarget',
                    'levels' => ['error', 'warning'],
                    'except' => ['yii\web\HttpException:*', 'yii\i18n\I18N\*'],
                    'prefix' => function () {
                        $url = !Yii::$app->request->isConsoleRequest ? Yii::$app->request->getUrl() : null;
                        return sprintf('[%s][%s]', Yii::$app->id, $url);
                    },
                    'logVars' => [],
                    'logTable' => '{{%sys_log}}'
                ]
            ],
        ],
        'i18n' => [
            'translations' => [
                '*' => [
                    'class' => yii\i18n\PhpMessageSource::class,
                    'basePath' => '@common/messages',
                    'sourceLanguage' => 'en',  //source to targeted language file
                    'forceTranslation' => true,
                    'fileMap' => [
                        'common' => 'common.php',
                        'backend' => 'backend.php',
                        'dashboard' => 'dashboard.php',
                        'frontend' => 'frontend.php',
                        'email' => 'email.php',
                    ],
                    'on missingTranslation' => [backend\modules\translation\Module::class, 'missingTranslation']
                ],
            ],
        ],
        'fileStorage' => [
            'class' => 'trntv\filekit\Storage',
            'baseUrl' => env("STORAGE_URL"),

            'filesystem'=> [
                'class' => 'common\components\filesystem\AwsS3v3FlysystemBuilder',
                'key' => env("AWS_KEY"),
                'secret' => env("AWS_SECRET"),
                'bucket' => env("AWS_S3_BUCKET"),
                'region' => env("AWS_S3_REGION"),
                
            ],
            'as log' => [
                'class' => 'common\behaviors\FileStorageLogBehavior',
                'component' => 'fileStorage'
            ]
        ],
        'myS3Client' => [
            'class' => 'common\components\filesystem\MyS3Client',
            'key' => env("AWS_KEY"),
            'secret' => env("AWS_SECRET"),
            'bucket' => env("AWS_S3_BUCKET"),
            'region' => env("AWS_S3_REGION")
        ],

        'keyStorage' => [
            'class' => common\components\keyStorage\KeyStorage::class
        ],

        'urlManagerApi' => \yii\helpers\ArrayHelper::merge(
            [
                'hostInfo' => Yii::getAlias('@apiUrl'),
                'baseUrl' => Yii::getAlias('@apiUrl'),
            ],
            require(Yii::getAlias('@api/config/_urlManager.php'))
        ),

        'urlManagerFrontend' => \yii\helpers\ArrayHelper::merge(
            [
                'hostInfo' => Yii::getAlias('@frontendUrl'),
                'baseUrl' => Yii::getAlias('@frontendUrl'),
            ],
            require(Yii::getAlias('@frontend/config/_urlManager.php'))
        ),

        'urlManagerBackend' => \yii\helpers\ArrayHelper::merge(
            [
                'hostInfo' => Yii::getAlias('@backendUrl'),
                'baseUrl' => Yii::getAlias('@backendUrl'),

            ],
            require(Yii::getAlias('@backend/config/_urlManager.php'))
        ),
        
        'urlManagerDashboard' => \yii\helpers\ArrayHelper::merge(
            [
                'hostInfo' => Yii::getAlias('@dashboardUrl'),
                'baseUrl' => Yii::getAlias('@dashboardUrl'),

            ],
            require(Yii::getAlias('@dashboard/config/_urlManager.php'))
        ),
        'queue' => [
            'class' => \yii\queue\db\Queue::class,
            'db' => 'db', // DB connection component or its config 
            'tableName' => '{{%sys_queue}}', // Table name
            'channel' => 'default', // Queue channel key
            'mutex' => \yii\mutex\MysqlMutex::class, // Mutex used to sync queries
            'as log' => \yii\queue\LogBehavior::class,
            'attempts' => 5,
            'ttr' => 5,
        ],
        'rabbitMq' => [
            'class' => \yii\queue\amqp_interop\Queue::class,
            //'port' => 5672,
            'port' => 5671,
            'queueName' => 'socket-notification',
            'driver' => yii\queue\amqp_interop\Queue::ENQUEUE_AMQP_LIB,
            'dsn' => env('MQ_URL'),
            //'dsn' => 'amqp://localhost',
            'strictJobType' => false,
            'serializer' => \yii\queue\serializers\JsonSerializer::class,
        ],
        //loynote: aws queue not suitable
        // 'awsSqs' => [
        //     'class' => \yii\queue\sqs\Queue::class,
        //     'key' => env("AWS_KEY"),
        //     'secret' => env("AWS_SECRET"),
        //     'url' => env('AWS_SQS_URL'),
        //     'region' => env('AWS_SQS_REGION'),            
        //     'strictJobType' => false,
        //     'serializer' => \yii\queue\serializers\JsonSerializer::class,
        // ],
        'pdf' => [
            'class' => \kartik\mpdf\Pdf::classname(),
            'format' => \kartik\mpdf\Pdf::FORMAT_A4,
            'orientation' => \kartik\mpdf\Pdf::ORIENT_PORTRAIT,
            'destination' => \kartik\mpdf\Pdf::DEST_BROWSER,
            // refer settings section for all configuration options
        ]

    ],
    'params' => [
        'bsVersion' => '3.x',// this will set globally `bsVersion` to Bootstrap 3.x for all Krajee Extensions
        'adminEmail' => env('ADMIN_EMAIL'),
        'adminEmailName' => 'Instaprotection Admin',
        'robotEmail' => env('ROBOT_EMAIL'),
        'robotEmailName' => 'Instaprotection System',
        'carbonCopyEmailList' => ['IP_ADMIN_EMAIL' => [''], 'SG' => [''], 'MY' => [], 'TH' => [], 'ID' => [], 'VN' => []],
        'availableLocales'=>['en', 'th-TH', 'vn-VN', 'en-MY', 'id-ID'],
        'languages'=>['en' => "English", 'th-TH' => "Thai", 'vn-VN' => "Viet", 'en-MY' => "Chinese", 'id-ID' => "Indonesia"],
        'ga_trackingId' => env('GA_TRACKING_ID'),

        'meta_copyright' => ['property' => 'copyright', 'content'=>'InstaProtection'],
        'meta_author' => ['property' => 'author', 'content'=>'admin, support@instaprotection.com'],
        'meta_reply-to' => ['property' => 'reply-to', 'content'=>'support@instaprotection.com'],
        'meta_description' => ['property' => 'description', 'content' => Yii::t('frontend','Best-in-class Care Plans that enable greater protection and peace of mind for you at affordable prices.')],
        'meta_keywords' => ['property' => 'keyword', 'content' => Yii::t('frontend','InstaProtection')],
        'og_url' => ['property' => 'og:url', 'content' => 'https://protect.instaprotection.com/'],
        'og_title' => ['property' => 'og:title', 'content' => 'InstaProtection'],
        'og_description' => ['property' => 'og:description', 'content' => Yii::t('frontend','Best-in-class Care Plans that enable greater protection and peace of mind for you at affordable prices.')],
        'og_image' => ['property' => 'og:image', 'content' => 'https://protect.instaprotection.com/img/logo-ip-square.png'],
        'og_type' => ['property' => 'og:type', 'content' => 'website'],
        'og_locale' => ['property' => 'og:image', 'content' => 'en_US'],
        'fb_app_id' => ['property' => 'og:image', 'content' => env('FACEBOOK_CLIENT_ID')],

        // 'jsonImavenFeature' => Yii::getAlias('@api/web/json/imaven-feature.geojson'),
        // 'jsonImavenHeritageTree' => Yii::getAlias('@api/web/json/imaven-heritage-tree.geojson'),
        //'JsOneMapToken' => Yii::getAlias('@backend/web/js/onemap.js'),
        //'JsOneMapPcnToken' => Yii::getAlias('@backend/web/js/onemap-pcn.js'),

        //for firebase
        //ref: https://github.com/googleapis/google-api-php-client
        //prod
        // 'googleApiJson' => Yii::getAlias('@common/config/nparks-coast-to-coast-firebase-adminsdk.json'), 
        //stage
        //'googleApiJson' => Yii::getAlias('@common/config/nparks-coast-to-coast-firebase-adminsdk-stage.json'), 
    ],
];

if (YII_ENV_PROD) {
    $config['components']['log']['targets']['email'] = [
        'class' => yii\log\EmailTarget::class,
       'except' => ['yii\web\HttpException:*'],
        'levels' => ['error'],
        'message' => ['from' => env('ROBOT_EMAIL'), 'to' => env('DEVELOPER_EMAIL')]
    ];
}

if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class'=>'yii\gii\Module',
        'generators' => [
            'job' => [
                'class' => \yii\queue\gii\Generator::class,
            ],
        ],
    ];
    //comment for file cache testing
    $config['components']['cache'] = [
        'class' => yii\caching\DummyCache::class
    ];
}

return $config;
