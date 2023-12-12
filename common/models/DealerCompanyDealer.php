<?php

namespace common\models;

use Yii;
use common\components\MyCustomActiveRecord;


class DealerCompanyDealer extends MyCustomActiveRecord
{
    public static function tableName()
    {
        return 'dealer_company_dealer';
    }

    public function rules()
    {
        return [
            [['dealer_company_upline_id', 'dealer_company_downline_id'], 'required'],
            [['dealer_company_upline_id', 'dealer_company_downline_id','id'], 'integer'],
            ['dealer_company_upline_id', 'compare', 'compareAttribute' => 'dealer_company_downline_id', 'operator' => '!=', 'message' => Yii::t("common",'Invalid assignment, upline and downline cannot be same')],
            ['dealer_company_downline_id', 'compare', 'compareAttribute' => 'dealer_company_upline_id', 'operator' => '!=', 'message' => Yii::t('common','Invalid assignment, upline and downline cannot be same')],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'dealer_company_upline_id' => Yii::t('common', 'Dealer Upline'),
            'dealer_company_downline_id' => Yii::t('common', 'Dealer Downline'),
        ];
    }

    public static function getUpline($id) {
        $res = SELF::find()->andWhere(['dealer_company_downline_id' => $id])->one();
        return $res;
        // $this->hasOne(DealerCompany::className(), ['dealer_company_downline_id' => 'dealer_company_downline_id']);
    }

    public static function getDownlineArray($id) {
        return SELF::find()->andWhere(['dealer_company_upline_id' => $id])->asArray()->all();
    }


    public static function find()
    {
        return new \common\models\query\DealerCompanyDealerQuery(get_called_class());
    }

    public static function getDealer($id) {
        $res = DealerCompany::find()->andWhere(['id' => $dealer_company_downline_id ])->one();
        return $res;
        // $this->hasOne(DealerCompany::className(), ['dealer_company_downline_id' => 'dealer_company_downline_id']);
    }

    public function getUplineComapny() {
        return $this->hasOne(DealerCompany::className(), ['id' => 'dealer_company_upline_id']);
    }
    public function getDownlineComapny() {
        return $this->hasOne(DealerCompany::className(), ['id' => 'dealer_company_downline_id']);
    }

    public function toObject() {
        $m = $this;

        $o = (object) [];
        // $o->dealer_company_id = $m->dealer_company_id;
        $o->upline_id = $m->dealer_company_upline_id;
        $o->downline_id = $m->dealer_company_downline_id;
        $o->upline_business_name = $m->uplineCompany->business_name;
        $o->downline_business_name = $m->downlineCompany->business_name;
        return $o;
    }

}
