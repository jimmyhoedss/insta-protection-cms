<?php

namespace common\models;

use Yii;


class UserPlanDetailEditHistory extends \yii\db\ActiveRecord
{
    const ACTION_EDIT_APPROVE = 'edit-approve';
    
    public static function tableName()
    {
        return 'user_plan_detail_edit_history';
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
            'id' => Yii::t('backend', 'ID'),
            'row_id' => Yii::t('backend', 'Row ID'),
            'model' => Yii::t('backend', 'Model'),
            'controller' => Yii::t('backend', 'Controller'),
            'action' => Yii::t('backend', 'Action'),
            'value' => Yii::t('backend', 'Value'),
            'created_by' => Yii::t('backend', 'Created By'),
            'created_at' => Yii::t('backend', 'Created At'),
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    //html layout
    public static function getPlanDetailEditHistoryLayout($models) {
        $html = "";
        foreach($models as $m) {
                // $user = "<b><i>" . utf8_decode($m->user->userProfile->fullName) . "</i></b>";
                $user = "<b><i>" . utf8_decode($m->user->getPublicIdentity()) . "</i></b>";
                $action = "<b>".$m->action."</b>";
                $date = Yii::$app->formatter->asDatetime($m->created_at);

                if ($m->action == UserPlanDetailEditHistory::ACTION_EDIT_APPROVE) {
                    $action = "<b> approve </b>";
                    $html .= "<i>" . $user . "&nbsp; ". $action ."&nbsp;at " . $date ." <br>"; 

                } else {
                    $html .= "<i>" . $user . "&nbsp; ". $action ."&nbsp;at " . $date ." <br>"; 
                }
            }
            return \yii\helpers\HtmlPurifier::process($html);
    }

}
