<?php
return [
    // 'class' => yii\web\UrlManager::class,
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'rules'=>[
        ['pattern'=>'<action>', 'route'=>'site/<action>'],
        //['pattern'=>'<controller>', 'route'=>'<controller>/index'],
    ],
    'class' => codemix\localeurls\UrlManager::class,
    'languages' => ['en', 'th-TH', 'vn-VN','zh-MY', 'id-ID'],
];
