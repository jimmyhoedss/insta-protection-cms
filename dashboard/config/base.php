<?php
return [
    'id' => 'dashboard',
    'basePath' => dirname(__DIR__),
    'components' => [
        'urlManager' => require __DIR__ . '/_urlManager.php',
        'frontendCache' => require Yii::getAlias('@frontend/config/_cache.php')
    ],
];
