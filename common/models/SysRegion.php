<?php

namespace common\models;

use Yii;


class SysRegion extends \yii\db\ActiveRecord
{
    const THAILAND = "TH";
    const VIETNAM = "VN";
    const MALAYSIA = "MY";
    const INDONESIA = "ID";
    const SINGAPORE = "SG";

    const MOBILE_NUMBER_SG = "+6598551971";
    const MOBILE_NUMBER_MY = "+60126934226";
    const MOBILE_NUMBER_VN = "+60126934226vn";
    const MOBILE_NUMBER_TH = "+60126934226th";
    const MOBILE_NUMBER_ID = "+60126934226id";

    const LINE_URL_TH = "https://line.me/R/oaMessage/@331ylqji/?Hi%20InstaProtection,%20i've%20some%20enquires";


    public static function tableName()
    {
        return 'sys_region';
    }

    public function rules()
    {
        return [
            [['id', 'name', 'calling_code'], 'required'],
            [['calling_code'], 'integer'],
            [['id'], 'string', 'max' => 8],
            [['name'], 'string', 'max' => 64],
            [['id'], 'unique'],
        ];
    }


    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'name' => Yii::t('common', 'Name'),
            'calling_code' => Yii::t('common', 'Calling Code'),
        ];
    }

    public static function mapLanguageToCountry($language) {
        $country = "";
        $locale = Yii::$app->params['availableLocales'];
        // ['en', 'th-TH', 'vn-VN', 'en-MY', 'id-ID']
        switch ($language) {
            case $locale[1]:
                $country = SysRegion::THAILAND;
                break;
            case $locale[2]:
                $country = SysRegion::VIETNAM;
                break;
            case $locale[3]:
                $country = SysRegion::MALAYSIA;
                break;
            case $locale[4]:
                $country = SysRegion::INDONESIA;
                break;
            default:
                $country = SysRegion::SINGAPORE;
                break;
        }

        return $country;

    }

    public static function mapCountryToNativeLanguage($region_id) {
        $locale = "";
        // ['en', 'th'=>'th-TH', 'vn'=>'vn-VN', 'my'=>'ms-MY', 'id'=>'id-ID']
        switch ($region_id) {
            case SysRegion::THAILAND:
                $locale = 'th-TH';
                break;
            case SysRegion::VIETNAM:
                $locale = 'vn-VN';
                break;
            case SysRegion::MALAYSIA:
                $locale = 'ms-MY';
                break;
            case SysRegion::INDONESIA:
                $locale = 'id-ID';
                break;
            default: // SysRegion::SINGAPORE
                $locale = 'en';
                break;
        }

        return $locale;

    }

    public static function getWhatsappBusinessUrl($region_id) {

        $url = "";
        switch ($region_id) {
            case SysRegion::SINGAPORE:
                $url = "https://wa.me/".SELF::MOBILE_NUMBER_SG."?text=Hi+Instaprotection%2C+I%27ve+some+enquires";
                break;
            case SysRegion::VIETNAM:
                $url = "https://wa.me/".SELF::MOBILE_NUMBER_VN."?text=Hi+Instaprotection%2C+I%27ve+some+enquires+vietnam";
                break;
            case SysRegion::MALAYSIA:
                $url = "https://wa.me/".SELF::MOBILE_NUMBER_MY."?text=Hi+Instaprotection%2C+I%27ve+some+enquires+malaysia";
                break;
            case SysRegion::THAILAND:
                $url = self::LINE_URL_TH; //for thailand use LINE instead
                break;
            default:
                $url = "https://wa.me/".SELF::MOBILE_NUMBER_SG."?text=Hi+Instaprotection%2C+I%27ve+some+enquires+default";
                break;
        }

        return $url;
    }

    public static function getContactNumber() {
        return [
            SELF::SINGAPORE => self::MOBILE_NUMBER_SG,
            SELF::VIETNAM => self::MOBILE_NUMBER_VN,
            SELF::MALAYSIA => self::MOBILE_NUMBER_MY,
            SELF::THAILAND => self::MOBILE_NUMBER_TH,
            SELF::INDONESIA => self::MOBILE_NUMBER_ID,
        ];
    }

    public static function getAllRegions() {
        return [
            SELF::SINGAPORE,
            // SELF::VIETNAM,
            SELF::MALAYSIA,
            // SELF::THAILAND,
            // SELF::INDONESIA,
        ];
    }

    // public static function getAddress() {
    //     return [
    //         SELF::SINGAPORE => self::MOBILE_NUMBER_SG,
    //         SELF::VIETNAM => self::MOBILE_NUMBER_VN,
    //         SELF::MALAYSIA => self::MOBILE_NUMBER_MY,
    //         SELF::THAILAND => self::MOBILE_NUMBER_TH,
    //         SELF::INDONESIA => self::MOBILE_NUMBER_ID,
    //     ];
    // }

}
