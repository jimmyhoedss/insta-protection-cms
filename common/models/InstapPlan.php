<?php

namespace common\models;

use Yii;
use common\components\MyCustomActiveRecord;
use common\components\Utility;
use common\models\InstapPlanPool;
use yii\helpers\ArrayHelper;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use common\behaviors\MyAuditTrailBehavior;
use common\models\SysRegion;

class InstapPlan extends MyCustomActiveRecord
{
    const SALES_CHANNEL_DEALER_TYPE1 = 'dealer_type1';//for add hoc base dealer
    const SALES_CHANNEL_DEALER_TYPE2 = 'dealer_type2';//for inventory base dealer
    //const SALES_CHANNEL_ROADSHOW = 'roadshow';
    //const SALES_CHANNEL_TELCO_BUNDLE = 'telco_bundle';

    //instap plan categories
    const ALL_CATEGORY = "all_category";

    const APPLIANCES = "AP" ;
    const HEALTH = "HL" ;
    const TRAVEL= "TR";
    const LIFESTYLE = "LS";

    //lifestyle subcategory
    const LIFESTYLE_LAPTOP = "LSLT";
    const LIFESTYLE_SMART_PHONE = "LSSP";
    const LIFESTYLE_SMARTWATCH_EARBUDS = "LSSE";
    const LIFESTYLE_TABLET = "LSTB";


    const SMART_PHONE = "SP";
    const BASIC = "basic";

    //healthcare subcategory
    //travel subcategory
    //appliances subcategory


    //instap plan tier
    const ALL_TIER = "all_tier";
    const BASIC_PLUS = "basic_plus";
    const STANDARD = "standard";
    const PREMIUM = "premium";



    public function init(){
        //$this->detachBehavior('MyLatlngPickerBehavior');
        parent::init();
    }

    public static function tableName() {
        return 'instap_plan';
    }
  
    public function rules()
    {
      return ArrayHelper::merge([
            [['region_id','coverage_period','name', 'sku', 'retail_price', 'premium_price', 'dealer_price', 'master_policy_number', 'pdf', 'thumbnail'], 'required'],
            // [['category', 'description', 'status', 'webview_url'], 'string'],
            [['category', 'tier', 'description', 'status', 'webview_url'], 'string'], //include tier
            [['retail_price', 'premium_price', 'dealer_price'], 'number'],
            [['coverage_period', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['sku','master_policy_number'], 'string', 'max' => 64],
            [['region_id'], 'string', 'max' => 8],
            [['name'], 'string', 'max' => 255],
            [['sku'], 'unique'],
            ['tier', 'in', 'range' => array_keys(self::allPlanTier())],
            ['category', 'in', 'range' => array_keys(self::allPlanCategory())],
            [['thumbnail_base_url', 'thumbnail_path', 'pdf_base_url', 'pdf_path'], 'string', 'max' => 1024],
            ['status', 'default', 'value' => MyCustomActiveRecord::STATUS_ENABLED],
        ], parent::rules());
    }

    public function attributeLabels() {
        return [
            'id' => Yii::t('common', 'ID'),
            'category' => Yii::t('common', 'Category'),
            'master_policy_number' => Yii::t('common', 'Master Policy Number'),
            'sku' => Yii::t('common', 'SKU'),
            'region_id' => Yii::t('common', 'Region ID'),
            'name' => Yii::t('common', 'Name'),
            'description' => Yii::t('common', 'Description'),
            'coverage_period' => Yii::t('common', 'Coverage Period'),
            'retail_price' => Yii::t('common', 'Retail Price'),
            'premium_price' => Yii::t('common', 'Premium Price'),
            'dealer_price' => Yii::t('common', 'Dealer Price'),
            'status' => Yii::t('common', 'Status'),
            'thumbnail_base_url' => 'Thumbnail Base Url',
            'thumbnail_path' => 'Thumbnail Path',
            'pdf_base_url' => 'PDF Base Url',
            'pdf_path' => 'PDF Path',
            'pdf' => 'Policy Details PDF',
            'created_at' => Yii::t('common', 'Created At'),
            'created_by' => Yii::t('common', 'Created By'),
            'updated_at' => Yii::t('common', 'Updated At'),
            'updated_by' => Yii::t('common', 'Updated By'),
        ];
    }

    public static function allSalesChannel()
    {
        return [
            //self::SALES_CHANNEL_DEALER => Yii::t('common','Dealer'),
            self::SALES_CHANNEL_DEALER_TYPE1 => Yii::t('common','Dealer (Ad Hoc)'),
            self::SALES_CHANNEL_DEALER_TYPE2 => Yii::t('common','Dealer (Inventory)'),
            //self::SALES_CHANNEL_ROADSHOW => Yii::t('common','Roadshow'),
            //self::SALES_CHANNEL_TELCO_BUNDLE => Yii::t('common','Telco Bundle'),
        ];
    }

   //for app flat list
    public function toObject() {
        $m = $this;

        $o = (object) [];
        $instap_plan = (object) [];
        $instap_plan->id = $m->id;
        $instap_plan->category = $m->category;
        $instap_plan->tier = $m->tier;
        $instap_plan->sku = $m->sku;
        // $instap_plan->plan_thumbnail = $this->planThumbnail;
        // $instap_plan->thumbnail_base_url = $m->thumbnail_base_url;
        $instap_plan->thumbnail_presigned = $m->thumbnail_path ? Utility::getPreSignedS3Url($m->thumbnail_path): "";
        $instap_plan->description = $m->description;
        $instap_plan->name = $m->name;
        $instap_plan->region_id = $m->region_id;
        $instap_plan->retail_price = number_format($m->retail_price, 2);
        $o->instap_plan = $instap_plan;

        return $o;
    }

    public static function categorizeByTier($models) {
        $d = [];
        if(!empty($models)) {
            foreach ($models as $m) {
                //form out tier object
                $tier = $m->tier;
                $d[$tier][] = $m->toObject();
            }
        }
        return $d;
    }

    public static function find() {
        return new \common\models\query\InstapPlanQuery(get_called_class());
    }


    
    //loynote: should this be in parent class?
    //MyCustomActiveRecord
    public function getPlanThumbnail() {
        $m = $this;
        return Utility::getPreSignedS3Url($m->thumbnail_path);
        // return $m->thumbnail_base_url."/".$m->thumbnail_path;
    }

    public function getPolicyPdf() {
        $m = $this;
        return Utility::getPreSignedS3Url($m->pdf_path);
        // return $m->thumbnail_base_url."/".$m->thumbnail_path;
    }

    public function getPlanBanner() {
        $m = $this;
        $src = Utility::getPreSignedS3Url($m->thumbnail_path);;

        $html = "<div>";
        $html .= "<div style='position:absolute; padding:0 5px 0 5px; background-color:rgba(255,255,255,0.5)'><b>".$m->name ."</b></div>";
        $html .= "<img class='photo plan' src='". $src ."'>";
        $html .= "<div>";
        return $html;
    }

    static public function currencySymbol() {
        return [
            SysRegion::THAILAND => "฿",
            SysRegion::VIETNAM => "₫",
            SysRegion::MALAYSIA => "RM",
            SysRegion::INDONESIA => "Rp",
            SysRegion::SINGAPORE => "$",
        ];
    }

    public static function allPlanCategory() {
        $arr1 = [
            self::ALL_CATEGORY => Yii::t('common', 'All category'),
            self::APPLIANCES => Yii::t('common', 'Appliances'),
            self::HEALTH => Yii::t('common', 'Health') ,
            self::TRAVEL => Yii::t('common', 'Travel') ,
            self::LIFESTYLE => Yii::t('common', 'Lifestyle') 
        ];
        return array_merge($arr1, self::lifestyleSubCategory());
    }

    public static function category() {
        return [
            // self::ALL_CATEGORY => Yii::t('common', 'All category'),
            self::APPLIANCES => Yii::t('common', 'Appliances'),
            self::HEALTH => Yii::t('common', 'Health') ,
            self::TRAVEL => Yii::t('common', 'Travel') ,
            self::LIFESTYLE => Yii::t('common', 'Lifestyle') 
        ];
    }

    public static function lifestyleSubCategory() {
        return [
            self::LIFESTYLE => Yii::t('common', 'Lifestyle') ,
            // self::HEALTH => Yii::t('common', 'Health') ,
            self::LIFESTYLE_LAPTOP => Yii::t('common','Lifestyle Laptop' ) ,
            self::LIFESTYLE_SMART_PHONE => Yii::t('common', 'Lifestyle Smart Phone') ,
            self::LIFESTYLE_SMARTWATCH_EARBUDS => Yii::t('common', 'Lifestyle Smartwatch Earbuds') ,
            self::LIFESTYLE_TABLET => Yii::t('common', 'Lifestyle Tablet'),

            // debug
            self::SMART_PHONE => Yii::t('common', 'Smart Phone') ,
        ];
    }

    // for instap-plan/create
    public static function allPlanCategories() {
        return [
            self::LIFESTYLE_LAPTOP => Yii::t('common','Lifestyle Laptop' ) ,
            self::LIFESTYLE_SMART_PHONE => Yii::t('common', 'Lifestyle Smart Phone') ,
            self::LIFESTYLE_SMARTWATCH_EARBUDS => Yii::t('common', 'Lifestyle Smartwatch Earbuds') ,
            self::LIFESTYLE_TABLET => Yii::t('common', 'Lifestyle Tablet') 
        ];
    }

    public static function allPlanTier() {
        return [
            self::ALL_TIER => Yii::t('common', 'All tier'),
            self::BASIC_PLUS => Yii::t('common', 'Basic +'),
            self::STANDARD => Yii::t('common', 'Standard'),
            self::PREMIUM => Yii::t('common', 'Premium'),

            // debug
            // self::BASIC => Yii::t('common', 'Basic')
        ];
    }

    public static function getSubCategory($category) {
        // $arr = []
        $arr = [];
        if($category == InstapPlan::ALL_CATEGORY) {
            $arr1 = [self::APPLIANCES, self::HEALTH,  self::TRAVEL];
            $arr = array_merge($arr1, array_keys(InstapPlan::lifestyleSubCategory()));
        } else if($category == InstapPlan::LIFESTYLE) {
           $arr = array_keys(InstapPlan::lifestyleSubCategory());
        // }else if($category == InstapPlan::APPLIANCES){
        //     $this->andWhere(['category' => $category, 'tier' => $tier]);
        // }else if($category == InstapPlan::HEALTH){
        //     $this->andWhere(['category' => $category, 'tier' => $tier]);
        // }else if($category == InstapPlan::TRAVEL){
        //     $this->andWhere(['category' => $category, 'tier' => $tier]);
        // 
        } else {
            $arr = $category;
        }
        return $arr;
    }


    

}