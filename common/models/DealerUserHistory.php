<?php

namespace common\models;

use Yii;
use common\models\User;
use common\models\DealerCompany;

use common\components\MyCustomActiveRecord;


class DealerUserHistory extends MyCustomActiveRecord
{
    
    const ACTION_REMOVE_ROLE = 'remove_role';
    const ACTION_ADD_ROLE = 'add_role';
    const ACTION_CHANGE_ROLE = 'change_role';


    public static function tableName()
    {
        return 'dealer_user_history';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'dealer_company_id','role'], 'required'],
            [['id', 'user_id', 'dealer_company_id', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['role','action'], 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'dealer_company_id' => Yii::t('app', 'Dealer Company ID'),
            'action' => Yii::t('app', 'Action'),
            'created_at' => Yii::t('app', 'Created At'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'updated_by' => Yii::t('app', 'Updated By'),
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getDealer()
    {
        return $this->hasOne(DealerCompany::className(), ['id' => 'dealer_company_id']);
    }
    

    public static function makeModel($id, $dealer_company_id, $action, $role){
            $du_history = new SELF();
            $du_history->user_id = $id;
            $du_history->dealer_company_id = $dealer_company_id;
            $du_history->action = $action;
            $du_history->role = $role;
            return $du_history;
    }

    public function toObject() {
        $m = $this;
        $u = $m->user;
        $up = $u->userProfile;
        
        $o = (object) [];
        $o->dealer_company_id = $m->dealer_company_id;
        $o->action = $this->renameAction($m->action);
        $o->role = $m->role;
        $o->first_name= utf8_decode($up->first_name);
        $o->last_name= utf8_decode($up->last_name);
        $o->avatar_url= $up->avatar;
        $o->mobile_number_full= $u->mobile_number_full;
        $o->created_at = $m->created_at;

        // $o = (object) array_merge((array) $o, (array) $u->userDetails);
        return $o;
    }

    public function renameAction($action) {
        $vars = [
            self::ACTION_ADD_ROLE => Yii::t('common', 'Added on'),
            self::ACTION_REMOVE_ROLE => Yii::t('common', 'Removed on'),
            self::ACTION_CHANGE_ROLE => Yii::t('common', 'Changed on'),
        ];

        return strtr($action, $vars);
    }

    /**
     * {@inheritdoc}
     * @return DealerUserHistoryQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\DealerUserHistoryQuery(get_called_class());
    }




    //*********** html layout ***********

    
    public static function getDealerUserHistoryLayout($models) {
        $html = "";
        foreach($models as $m) {
            $user = User::find()->andWhere(['id'=>$m->created_by])->one();
            $dealer = DealerCompany::find()->andWhere(["id"=>$m->dealer_company_id])->one();
            $company = "<b> ".$dealer->business_name. "</b>";
            $from = "<b>" .$m->user->getPublicIdentity() . "</b>";
            $by = "<b>" .$user->getPublicIdentity(). "</b>";
            $date = Yii::$app->formatter->asDatetime($m->created_at);
            $action = "<b>".$m->action."</b>";
            $str = $m->role;
            // $str = substr($str, 0, -1);
            $role_display = "<span style='color:blue;'> [ ".str_replace('_', ' ',$str)." ]</span>";

            if($m->action == DealerUserHistory::ACTION_REMOVE_ROLE){
                $action = "removed from";
                $html .= "<i>" . $date . "&nbsp; :  " .$from. "&nbsp;was ". $action ." company". $company ." as a ".$role_display. "&nbsp; by " .$by." <br>"; 
            }
            if($m->action == DealerUserHistory::ACTION_ADD_ROLE){
                $action = "added to ";  
                $html .= "<i>" . $date . "&nbsp; :  " .$from . "&nbsp;was ". $action ."company". $company ." as a ".$role_display. "&nbsp; by " .$by." <br>";
            }
            if($m->action == DealerUserHistory::ACTION_CHANGE_ROLE){
                $action = "change to"; 
                $html .= "<i>" . $date . "&nbsp; :  " .$from . "&nbsp; was ". $action .$role_display. $company . "&nbsp; by " .$by. " in company" .$company." <br>";
            }

        }
        return \yii\helpers\HtmlPurifier::process($html);
    }
}
