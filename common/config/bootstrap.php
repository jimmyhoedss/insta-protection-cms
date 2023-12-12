<?php
/**
 * Require core files
 */
require_once(__DIR__ . '/../helpers.php');

/**
 * Setting path aliases
 */
Yii::setAlias('@base', realpath(__DIR__.'/../../'));
Yii::setAlias('@api', realpath(__DIR__.'/../../api'));
Yii::setAlias('@frontend', realpath(__DIR__.'/../../frontend'));
Yii::setAlias('@backend', realpath(__DIR__.'/../../backend'));
Yii::setAlias('@dashboard', realpath(__DIR__.'/../../dashboard'));
Yii::setAlias('@insurer', realpath(__DIR__.'/../../insurer'));
Yii::setAlias('@common', realpath(__DIR__.'/../../common'));
Yii::setAlias('@console', realpath(__DIR__.'/../../console'));
Yii::setAlias('@tests', realpath(__DIR__.'/../../tests'));

/**
 * Setting url aliases
 */
Yii::setAlias('@apiUrl', env('API_URL'));
Yii::setAlias('@frontendUrl', env('FRONTEND_URL'));
Yii::setAlias('@backendUrl', env('BACKEND_URL'));
Yii::setAlias('@dashboardUrl', env('DASHBOARD_URL'));
//Yii::setAlias('@insurerUrl', env('INSURER_URL'));
Yii::setAlias('@storageUrl', env('STORAGE_URL'));
//Yii::setAlias('@cmsUrl', env('CMS_URL'));
