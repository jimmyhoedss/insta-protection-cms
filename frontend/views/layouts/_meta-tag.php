<?php

/*
$descShort = 'Trek the Himalayas with FIRST HIMALAYAN. Go on an epic trek up the majestic mountains in Nepal and have the memories of adventures that will last a lifetime.';

$desc = 'At FIRST HIMALAYAN, we specialize in adventure travelling as we believe in seeking out experiences that add meaning to our lives. Explore an exotic city and immerse yourself in a totally different culture, or go for on an epic trek up a majestic mountain in Nepal; journeys like these are often deeply enriching and unforgettable. We want your travels to become memories that will last for a long time.';


$this->registerMetaTag([
    'name' => 'description',
    'content' => $descShort,
], 'description');

$this->registerMetaTag([
    'name' => 'keywords',
    'content' => 'first himalayan, himalaya, himalayas, himalayan, trek, trekking, everest, annapurna, langtang, abc trek, ebc trek, annapurna circuit, poon hill, pokhara, kathmandu, lukla, nepal, nepal treks, himalaya trekking, best treks, guided trek, day tour, guesthouse trek',
], 'keyword');

$this->registerMetaTag(['property' => 'og:url', 'content' => "https://firsthimalayan.com/"], "og:url");
$this->registerMetaTag(['property' => 'og:title', 'content' => $this->title], "og:title");
$this->registerMetaTag(['property' => 'og:description', 'content' => $desc], "og:description");
$this->registerMetaTag(['property' => 'og:image', 'content' => 'https://firsthimalayan.com/img/siteImage.jpg'], "og:image");
$this->registerMetaTag(['property' => 'fb:app_id', 'content' => env('FACEBOOK_CLIENT_ID')], "og:app_id");

*/




$this->registerMetaTag(['name' => 'description', 'content' => Yii::$app->params['meta_description']['content'] ], 'description');
$this->registerMetaTag(['name' => 'keywords', 'content' => Yii::$app->params['meta_keywords']['content'] ], 'keywords');
$this->registerMetaTag(['name' => 'copyright', 'content' => Yii::$app->params['meta_copyright']['content'] ], 'copyright');
$this->registerMetaTag(['name' => 'author', 'content' => Yii::$app->params['meta_author']['content'] ], 'author');
$this->registerMetaTag(['name' => 'reply-to', 'content' => Yii::$app->params['meta_reply-to']['content'] ], 'reply-to');

$this->registerMetaTag(['property' => 'og:url', 'content' => Yii::$app->params['og_url']['content'] ], "og:url");
$this->registerMetaTag(['property' => 'og:title', 'content' => Yii::$app->params['og_title']['content'] ], "og:title");
$this->registerMetaTag(['property' => 'og:description', 'content' => Yii::$app->params['og_description']['content'] ], "og:description");
$this->registerMetaTag(['property' => 'og:image', 'content' => Yii::$app->params['og_image']['content'] ] , "og:image");
$this->registerMetaTag(['property' => 'og:type', 'content' => Yii::$app->params['og_type']['content'] ] , "og:type");
$this->registerMetaTag(['property' => 'og:locale', 'content' => Yii::$app->language] , "og:locale");
$this->registerMetaTag(['property' => 'fb:app_id', 'content' => Yii::$app->params['fb_app_id']['content'] ] , "fb:app_id");
//$this->registerMetaTag(['property' => 'fb:app_id', 'content' => env('FACEBOOK_CLIENT_ID')], "fb:app_id");





?>



    


