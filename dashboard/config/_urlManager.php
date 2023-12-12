<?php
return [
    'class' => codemix\localeurls\UrlManager::class,
    // 'languages' => ['en', 'th'=>'th-TH', 'vn'=>'vn-VN', 'my'=>'ms-MY', 'id'=>'id-ID'],
    'languages' => ['en'=> 'en', 'th'=>'th-TH', 'vn'=>'vn-VN', 'my'=>'ms-MY', 'en-my' => 'en-MY', 'id'=>'id-ID'],
     // 'class' => yii\web\UrlManager::class,
    'enableDefaultLanguageUrlCode' => false,
    'enableLanguagePersistence' => true,
    'enablePrettyUrl'=> true,
    'showScriptName'=> false,
    'keepUppercaseLanguageCode' => false,
    'rules'=>[
    	// catch all
        //['pattern'=>'nparks-promotion/<action>', 'route'=>'nparks-promotion/<action>'],
        ['pattern'=>'<action>', 'route'=>'site/<action>'],
        //['pattern'=>'<controller>', 'route'=>'<controller>/index'],
    ],
    // 'class' => codemix\localeurls\UrlManager::class,
    // 'languages' => ['en', 'th-TH', 'vn-VN','zh-MY'],
];
