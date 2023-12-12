<?php
/**
 * @author Eugene Terentev <eugene@terentev.net>
 */

use common\components\keyStorage\FormWidget;
use backend\widgets\TabMenuSettingsWidget;
use common\models\KeyStorageItem;

/**
 * @var $model \common\components\keyStorage\FormModel
 */

$this->title = Yii::t('backend', 'Application settings');


echo TabMenuSettingsWidget::widget(['page'=>$page]);
echo FormWidget::widget([
    'model' => $model,
    'formClass' => '\yii\bootstrap\ActiveForm',
    'submitText' => Yii::t('backend', 'Save'),
    'submitOptions' => ['class' => 'btn btn-primary'],
]);


/*
$d = Yii::$app->keyStorage->getAll([
    KeyStorageItem::APP_MAINTENANCE_MODE,
    KeyStorageItem::APP_MAINTENANCE_MESSAGE,
    KeyStorageItem::APP_ANNOUNCEMENT_MODE,
    KeyStorageItem::APP_ANNOUNCEMENT_MESSAGE,
    KeyStorageItem::APP_VERSION_ANDROID,
    KeyStorageItem::APP_VERSION_IOS,
    KeyStorageItem::APP_VERSION_ANDROID_DEPRECATE,
    KeyStorageItem::APP_VERSION_IOS_DEPRECATE,
]);

print_r($d);
*/
?>