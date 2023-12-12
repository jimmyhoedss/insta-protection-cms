<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sys_audit_trail".
 *
 * @property int $id
 * @property int $row_id
 * @property string $model
 * @property string $controller
 * @property string $action
 * @property string $value
 * @property int $created_by
 * @property int $created_at
 */
class SysAuditTrail extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sys_audit_trail';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['row_id'], 'required'],
            [['row_id', 'created_by', 'created_at'], 'integer'],
            [['value'], 'string'],
            [['model', 'controller', 'action'], 'string', 'max' => 256],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'row_id' => Yii::t('common', 'Row ID'),
            'model' => Yii::t('common', 'Model'),
            'controller' => Yii::t('common', 'Controller'),
            'action' => Yii::t('common', 'Action'),
            'value' => Yii::t('common', 'Value'),
            'created_by' => Yii::t('common', 'Created By'),
            'created_at' => Yii::t('common', 'Created At'),
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    public static function leaveTrail($row_id, $model) {
        //loynote::passing in $row_id since form models does not have id
        $m = new SysAuditTrail();
        $m->row_id = $row_id;
        $m->model = $model->className();
        $m->controller = Yii::$app->controller->id;
        $m->action = Yii::$app->controller->action->id;
        $m->value = json_encode($model->attributes);
        $m->created_at = time();

        $user = Yii::$app->get('user', false);
        $m->created_by = $user && !$user->isGuest ? $user->id : null;

        return $m->save(false);
    }
}
