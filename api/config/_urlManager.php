<?php
return [
   // 'class' => 'yii\web\UrlManager',
    'class' => codemix\localeurls\UrlManager::class,
    // 'languages' => ['en', 'th-TH', 'zh-MY', 'id-ID'],
    // 'languages' => ['en', 'th'=>'th-TH', 'vn'=>'vn-VN', 'my'=>'zh-MY', 'id'=>'id-ID'],
    'enablePrettyUrl' => true,
    'enableStrictParsing' => true,
    'showScriptName' => false,
    'rules' => [

        ['pattern'=>'v1/document/<path:.+?>', 'route'=>'site/document'],

        ['pattern'=>'', 'route'=>'site/index'],
        ['pattern'=>'v1', 'route'=>'site/index'],
        ['pattern'=>'v1/user', 'route'=>'user/index'],
        ['pattern'=>'v1/user/<action>', 'route'=>'user/<action>'],
        ['pattern'=>'v1/dealer-company', 'route'=>'dealer-company/index'],
        ['pattern'=>'v1/dealer-company/<action>', 'route'=>'dealer-company/<action>'],
        ['pattern'=>'v1/company-inventory', 'route'=>'company-inventory/index'],
        ['pattern'=>'v1/company-inventory/<action>', 'route'=>'company-inventory/<action>'],
        ['pattern'=>'v1/instap', 'route'=>'instap/index'],
        ['pattern'=>'v1/instap/<action>', 'route'=>'instap/<action>'],
        ['pattern'=>'v1/utils', 'route'=>'utils/index'],
        ['pattern'=>'v1/utils/<action>', 'route'=>'utils/<action>'],
        ['pattern'=>'v1/sys', 'route'=>'sys/index'],
        ['pattern'=>'v1/sys/<action>', 'route'=>'sys/<action>'],
        ['pattern'=>'v1/qcd', 'route'=>'qcd/index'],
        ['pattern'=>'v1/qcd/<action>', 'route'=>'qcd/<action>'],

        
        ['pattern'=>'v1/<action>', 'route'=>'site/<action>'],
        
    ],
    // 'ignoreLanguageUrlPatterns' => [
        // route pattern => url pattern
    //     '#^api/#' => '#^api/#',
    // ]
];
