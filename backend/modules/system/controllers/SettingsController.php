<?php

namespace backend\modules\system\controllers;


use common\components\keyStorage\FormModel;
use common\models\KeyStorageItem;
use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use common\models\User;



class SettingsController extends Controller
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                       //'actions' => ['index'],
                       'allow' => true,
                       'roles' => [User::ROLE_ADMINISTRATOR],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $model = new FormModel([
            'keys' => [
                KeyStorageItem::APP_MAINTENANCE_MODE => [
                    'label' => ucwords(str_replace('_', ' ', KeyStorageItem::APP_MAINTENANCE_MODE)),
                    'type' => FormModel::TYPE_DROPDOWN,
                    'items' => [
                        'disabled' => Yii::t('backend', 'Disabled'),
                        'enabled' => Yii::t('backend', 'Enabled'),
                    ],
                ],
                KeyStorageItem::APP_MAINTENANCE_MESSAGE => [
                    'label' => ucwords(str_replace('_', ' ', KeyStorageItem::APP_MAINTENANCE_MESSAGE)),
                    'type' => FormModel::TYPE_TEXTINPUT,
                ],
                KeyStorageItem::APP_ANNOUNCEMENT_MODE => [
                    'label' => ucwords(str_replace('_', ' ', KeyStorageItem::APP_ANNOUNCEMENT_MODE)),
                    'type' => FormModel::TYPE_DROPDOWN,
                    'items' => [
                        'disabled' => Yii::t('backend', 'Disabled'),
                        'enabled' => Yii::t('backend', 'Enabled'),
                    ],
                ],
                KeyStorageItem::APP_ANNOUNCEMENT_MESSAGE => [
                    'label' => ucwords(str_replace('_', ' ', KeyStorageItem::APP_ANNOUNCEMENT_MESSAGE)),
                    'type' => FormModel::TYPE_TEXTINPUT,
                ],
                KeyStorageItem::APP_VERSION_ANDROID => [
                    'label' => ucwords(str_replace('_', ' ', KeyStorageItem::APP_VERSION_ANDROID)),
                    'type' => FormModel::TYPE_TEXTINPUT,
                ],
                KeyStorageItem::APP_VERSION_ANDROID_DEPRECATE => [
                    'label' => ucwords(str_replace('_', ' ', KeyStorageItem::APP_VERSION_ANDROID_DEPRECATE)),
                    'type' => FormModel::TYPE_TEXTINPUT,
                ],
                KeyStorageItem::APP_VERSION_IOS => [
                    'label' => ucwords(str_replace('_', ' ', KeyStorageItem::APP_VERSION_IOS)),
                    'type' => FormModel::TYPE_TEXTINPUT,
                ],
                KeyStorageItem::APP_VERSION_IOS_DEPRECATE => [
                    'label' => ucwords(str_replace('_', ' ', KeyStorageItem::APP_VERSION_IOS_DEPRECATE)),
                    'type' => FormModel::TYPE_TEXTINPUT,
                ],
            ],
        ]);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('alert', [
                'body' => Yii::t('backend', 'Settings was successfully saved'),
                'options' => ['class' => 'alert alert-success'],
            ]);

            return $this->refresh();
        }

        return $this->render('index', ['model' => $model, 'page' => "app"]);
    }

    /*
    public function actionWebsite()
    {
        $model = new FormModel([
            'keys' => [
                KeyStorageItem::FRONTEND_MAINTENANCE_MODE => [
                    'label' => ucwords(str_replace('_', ' ', KeyStorageItem::FRONTEND_MAINTENANCE_MODE)),
                    'type' => FormModel::TYPE_DROPDOWN,
                    'items' => [
                        'disabled' => Yii::t('backend', 'Disabled'),
                        'enabled' => Yii::t('backend', 'Enabled'),
                    ],
                ],
                KeyStorageItem::BACKEND_MAINTENANCE_MODE => [
                    'label' => ucwords(str_replace('_', ' ', KeyStorageItem::BACKEND_MAINTENANCE_MODE)),
                    'type' => FormModel::TYPE_DROPDOWN,
                    'items' => [
                        'disabled' => Yii::t('backend', 'Disabled'),
                        'enabled' => Yii::t('backend', 'Enabled'),
                    ],
                ],
                KeyStorageItem::DASHBOARD_MAINTENANCE_MODE => [
                    'label' => ucwords(str_replace('_', ' ', KeyStorageItem::DASHBOARD_MAINTENANCE_MODE)),
                    'type' => FormModel::TYPE_DROPDOWN,
                    'items' => [
                        'disabled' => Yii::t('backend', 'Disabled'),
                        'enabled' => Yii::t('backend', 'Enabled'),
                    ],
                ],
            ],
        ]);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('alert', [
                'body' => Yii::t('backend', 'Settings was successfully saved'),
                'options' => ['class' => 'alert alert-success'],
            ]);

            return $this->refresh();
        }

        return $this->render('index', ['model' => $model, 'page' => "website"]);
    }
    */

}