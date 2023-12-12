<?php
return [
    //'class'=>'yii\web\UrlManager',
    'class' => codemix\localeurls\UrlManager::class,
    'languages' => ['en', 'th'=>'th-TH', 'vn'=>'vn-VN', 'my'=>'ms-MY', 'en-my'=>'en-MY', 'id'=>'id-ID'],
    'enableDefaultLanguageUrlCode' => false,
    'enableLanguagePersistence' => true,
    'enablePrettyUrl'=> true,
    'showScriptName'=> false,
    'keepUppercaseLanguageCode' => false,
    // 'geoIpLanguageCountries' => [
    //     'th' => ['SG'],
    //     'pt' => ['PRT', 'BRA'],
    // ],
    'rules'=> [

  //       ['pattern'=>'index', 'route'=>'site/index'],
  //       ['pattern'=>'app', 'route'=>'site/app'],
		// ['pattern'=>'activate', 'route'=>'site/activate'],
  //       ['pattern'=>'download', 'route'=>'site/download'],
        ['pattern'=>'choose-your-language', 'route'=>'site/language'],
        ['pattern'=>'<action>', 'route'=>'site/<action>'],
        ['pattern'=>'user/<action>', 'route'=>'user/<action>'],
        ['pattern'=>'terms', 'route'=>'term'],
        // ['pattern'=>'terms/my', 'route'=>'termMy'],
        // ['pattern'=>'terms-of-service/<action>', 'route'=>'term/<action>'],
		// ['pattern'=>'terms', 'route'=>'site/terms'],
		// ['pattern'=>'privacy', 'route'=>'site/privacy'],
        

		//['pattern'=>'<action>', 'route'=>'user/<action>'],
        	// ['pattern'=>'<action>', 'route'=>'site/<action>'],

        

    ]
];
